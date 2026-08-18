<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — backend/services/SchedulingEngine.php
//  Orchestrates the full scheduling pipeline:
//    1. Collect pending bookings for target date
//    2. Score each booking (TripScorer)
//    3. Cluster by geography (GeoHelper)
//    4. Optimise route per cluster (nearest-neighbour)
//    5. Assign cluster to available team
//    6. Persist draft schedules + stops
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

require_once __DIR__ . '/GeoHelper.php';
require_once __DIR__ . '/TripScorer.php';

class SchedulingEngine
{
    private TripScorer $scorer;

    public function __construct(private readonly PDO $pdo)
    {
        $this->scorer = new TripScorer(
            depotLat:  (float)($_ENV['DEPOT_LAT']       ?? 14.3294),
            depotLng:  (float)($_ENV['DEPOT_LNG']       ?? 120.9367),
            minScore:  (float)($_ENV['MIN_TRIP_SCORE']  ?? 10)
        );
    }

    /**
     * Run the full engine for a given date (Y-m-d).
     * Returns summary array.
     */
    public function run(string $date): array
    {
        $depotLat     = (float)($_ENV['DEPOT_LAT']      ?? 14.3294);
        $depotLng     = (float)($_ENV['DEPOT_LNG']      ?? 120.9367);
        $maxDailyHrs  = (float)($_ENV['MAX_DAILY_HOURS'] ?? 8);

        // ── Step 1: Collect pending bookings for this date ──
        $stmt = $this->pdo->prepare('
            SELECT
                b.id, b.client_id, b.address, b.latitude, b.longitude,
                b.preferred_date_from, b.preferred_date_to, b.notes,
                COALESCE(SUM(bs.quantity), 0)                                   AS total_qty,
                COALESCE(SUM(s.duration_hrs * bs.quantity), 0)                  AS total_hrs
            FROM bookings b
            LEFT JOIN booking_services bs ON bs.booking_id = b.id
            LEFT JOIN services s          ON s.id = bs.service_id
            WHERE b.status = "pending"
              AND b.preferred_date_from <= :dt
              AND b.preferred_date_to   >= :dt
            GROUP BY b.id
        ');
        $stmt->execute([':dt' => $date]);
        $bookings = $stmt->fetchAll();

        if (empty($bookings)) {
            return [
                'schedules_created'  => 0,
                'bookings_scheduled' => 0,
                'bookings_deferred'  => 0,
                'schedules'          => [],
            ];
        }

        // ── Step 2: Score each booking; split eligible vs deferred ──
        $eligible = [];
        $deferred = [];

        foreach ($bookings as $b) {
            $score = $this->scorer->score($b);
            // Persist score to DB
            $this->pdo->prepare('UPDATE bookings SET trip_score = ? WHERE id = ?')
                      ->execute([$score, $b['id']]);
            $b['trip_score'] = $score;

            if ($this->scorer->meetsThreshold($score)) {
                $eligible[] = $b;
            } else {
                $deferred[] = $b;
            }
        }

        // ── Step 3: Geographic clustering ──
        $clusters = GeoHelper::cluster($eligible, 5.0);

        // ── Step 4 & 5: Route optimise + assign team ──
        $availableTeams = $this->fetchAvailableTeams();
        $usedTeamIds    = [];

        $schedulesCreated  = 0;
        $bookingsScheduled = 0;
        $schedulesSummary  = [];
        $extraDeferred     = [];

        foreach ($clusters as $cluster) {
            // Nearest-neighbour route
            $route = GeoHelper::nearestNeighbour($cluster, $depotLat, $depotLng);

            // Total estimated hours for this route
            $totalHrs = array_sum(array_column($route, 'total_hrs'));

            // Find an available team that fits
            $assignedTeam = null;
            foreach ($availableTeams as $team) {
                if (in_array($team['id'], $usedTeamIds, true)) continue;
                if ($totalHrs <= $maxDailyHrs) {
                    $assignedTeam = $team;
                    break;
                }
            }

            if ($assignedTeam === null) {
                // No team available — defer whole cluster
                foreach ($route as $b) $extraDeferred[] = $b;
                continue;
            }

            $usedTeamIds[] = $assignedTeam['id'];

            // ── Step 6: Persist draft schedule ──
            $totalDistKm = $this->calcRouteDistanceKm($route, $depotLat, $depotLng);
            $scheduleId  = $this->uuid();

            $this->pdo->prepare('
                INSERT INTO schedules (id, scheduled_date, team_id, status, total_distance_km)
                VALUES (?, ?, ?, "draft", ?)
            ')->execute([$scheduleId, $date, $assignedTeam['id'], $totalDistKm]);

            // Insert stops + update bookings
            $eta = strtotime($date . ' 08:00:00'); // 8 AM start
            foreach ($route as $order => $b) {
                $stopId = $this->uuid();
                $etaStr = date('H:i:s', $eta);

                $this->pdo->prepare('
                    INSERT INTO schedule_stops (id, schedule_id, booking_id, stop_order, eta)
                    VALUES (?, ?, ?, ?, ?)
                ')->execute([$stopId, $scheduleId, $b['id'], $order + 1, $etaStr]);

                $this->pdo->prepare('
                    UPDATE bookings SET status = "scheduled", schedule_id = ? WHERE id = ?
                ')->execute([$scheduleId, $b['id']]);

                // Advance ETA by this job's hours + 30 min travel buffer
                $eta += (int)(((float)$b['total_hrs'] + 0.5) * 3600);

                $bookingsScheduled++;
            }

            $schedulesCreated++;
            $schedulesSummary[] = [
                'id'         => $scheduleId,
                'team_id'    => $assignedTeam['id'],
                'team_name'  => $assignedTeam['name'],
                'stop_count' => count($route),
            ];
        }

        return [
            'schedules_created'  => $schedulesCreated,
            'bookings_scheduled' => $bookingsScheduled,
            'bookings_deferred'  => count($deferred) + count($extraDeferred),
            'schedules'          => $schedulesSummary,
        ];
    }

    // ── Private helpers ──────────────────────────────────

    private function fetchAvailableTeams(): array
    {
        return $this->pdo->query('SELECT * FROM teams WHERE is_available = TRUE ORDER BY created_at ASC')
                         ->fetchAll();
    }

    private function calcRouteDistanceKm(array $route, float $depotLat, float $depotLng): float
    {
        $total  = 0.0;
        $curLat = $depotLat;
        $curLng = $depotLng;

        foreach ($route as $b) {
            if ($b['latitude'] === null) continue;
            $total += GeoHelper::distanceKm($curLat, $curLng, (float)$b['latitude'], (float)$b['longitude']);
            $curLat = (float)$b['latitude'];
            $curLng = (float)$b['longitude'];
        }

        return round($total, 2);
    }

    private function uuid(): string
    {
        // RFC-4122 v4 UUID
        $data    = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}