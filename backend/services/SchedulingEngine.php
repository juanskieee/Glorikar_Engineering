<?php
/**
 * SchedulingEngine.php — orchestrates the full scheduling pipeline.
 *
 *  Step 1  ClusterService    — group pending bookings by proximity
 *  Step 2  TripScorer        — score each booking
 *  Step 3  RouteOptimizer    — nearest-neighbor route from depot
 *  Step 4  Team assignment   — available team, fits 8-hour day
 *  Step 5  (admin approval happens on the Route Map page)
 *
 * Creates `schedules` rows with status 'draft'.
 */

namespace Glorikar\Services;

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/ClusterService.php';
require_once __DIR__ . '/TripScorer.php';
require_once __DIR__ . '/RouteOptimizer.php';

class SchedulingEngine
{
    private \PDO $pdo;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? db();
    }

    /**
     * Run the engine for a given window of upcoming dates.
     *
     * @param string|null $dateFrom Y-m-d lower bound (defaults to today)
     * @param string|null $dateTo   Y-m-d upper bound (defaults to +7 days)
     * @return array summary of what happened
     */
    public function run(?string $dateFrom = null, ?string $dateTo = null): array
    {
        $dateFrom = $dateFrom ?? date('Y-m-d');
        $dateTo = $dateTo ?? date('Y-m-d', strtotime('+7 days'));

        $summary = ['schedules_created' => 0, 'bookings_scheduled' => 0, 'bookings_deferred' => 0, 'errors' => []];

        // --- Step 1: gather pending bookings in window ----------------------
        $bookings = dball(
            "SELECT b.*,
                    COALESCE(SUM(bs.quantity), 0) AS services_count
               FROM bookings b
               LEFT JOIN booking_services bs ON bs.booking_id = b.id
              WHERE b.status = 'pending'
                AND b.preferred_date_from <= :to
                AND b.preferred_date_to >= :from
              GROUP BY b.id
              ORDER BY b.preferred_date_from ASC",
            [':from' => $dateFrom, ':to' => $dateTo]
        );

        if (!$bookings) {
            return $summary;
        }

        $depotLat = (float)\Env::get('DEPOT_LAT', 14.6091);
        $depotLng = (float)\Env::get('DEPOT_LNG', 121.0223);

        // --- Step 1b: cluster ----------------------------------------------
        $clusterService = new ClusterService(5.0);
        $clusters = $clusterService->cluster($bookings);

        // --- Step 2: score & partition -------------------------------------
        $anchorables = [];   // score >= MIN_TRIP_SCORE
        $deferred = [];      // below threshold -> wait for a nearby route

        foreach ($clusters as $cluster) {
            foreach ($cluster as $booking) {
                $dist = MapboxService::haversineKm(
                    $depotLat, $depotLng,
                    (float)$booking['latitude'], (float)$booking['longitude']
                );
                $score = TripScorer::score($booking, (int)$booking['services_count'], $dist);
                $booking['trip_score'] = $score;
                if (TripScorer::isAnchorable($score)) {
                    $anchorables[] = $booking;
                } else {
                    $deferred[] = $booking;
                    $summary['bookings_deferred']++;
                }
            }
        }

        // --- Step 3: route per cluster of anchorables ----------------------
        $routes = [];
        foreach ($clusterService->cluster($anchorables) as $cluster) {
            // Sort by preferred date first, then optimize within same date.
            $byDate = [];
            foreach ($cluster as $b) {
                $byDate[$b['preferred_date_from']][] = $b;
            }
            ksort($byDate);
            foreach ($byDate as $date => $sameDay) {
                $routes[] = [
                    'date' => $date,
                    'stops' => RouteOptimizer::optimize($sameDay, $depotLat, $depotLng),
                ];
            }
        }

        // --- Step 4: team assignment & draft creation ----------------------
        foreach ($routes as $route) {
            $totalHrs = 0;
            foreach ($route['stops'] as $b) {
                $totalHrs += $this->bookingDurationHours($b['id']);
            }

            $maxHours = (float)\Env::get('MAX_DAILY_HOURS', 8);
            if ($totalHrs > $maxHours) {
                $summary['errors'][] = "Route on {$route['date']} exceeds {$maxHours}h ({$totalHrs}h) — skipped.";
                $summary['bookings_deferred'] += count($route['stops']);
                continue;
            }

            $team = $this->assignTeam($route['date'], $totalHrs);
            if (!$team) {
                $summary['errors'][] = "No available team for {$route['date']} — deferred.";
                $summary['bookings_deferred'] += count($route['stops']);
                continue;
            }

            $scheduleId = $this->createDraftSchedule($route['date'], $team, $route['stops'], $depotLat, $depotLng);
            if ($scheduleId) {
                $summary['schedules_created']++;
                $summary['bookings_scheduled'] += count($route['stops']);
            }
        }

        // Persist scores for deferred + scheduled bookings (informational).
        foreach (array_merge($anchorables, $deferred) as $b) {
            dbq('UPDATE bookings SET trip_score = ? WHERE id = ?', [$b['trip_score'], $b['id']]);
        }

        return $summary;
    }

    /** Total duration hours of all services on a booking. */
    private function bookingDurationHours(string $bookingId): float
    {
        $row = dbfetch(
            'SELECT COALESCE(SUM(s.duration_hrs * bs.quantity), 0) AS hrs
               FROM booking_services bs
               JOIN services s ON s.id = bs.service_id
              WHERE bs.booking_id = ?',
            [$bookingId]
        );
        return (float)($row['hrs'] ?? 0);
    }

    /** Pick an available team for a date that isn't already over-booked. */
    private function assignTeam(string $date, float $hours): ?array
    {
        $teams = dball('SELECT * FROM teams WHERE is_available = TRUE ORDER BY created_at ASC');
        foreach ($teams as $team) {
            $existing = dbfetch(
                'SELECT COALESCE(SUM(COALESCE(
                      (SELECT COALESCE(SUM(s.duration_hrs * bs.quantity),0)
                         FROM booking_services bs JOIN services s ON s.id = bs.service_id
                        WHERE bs.booking_id = ss.booking_id), 0)), 0) AS hrs
                   FROM schedule_stops ss
                   JOIN schedules s ON s.id = ss.schedule_id
                  WHERE s.team_id = ? AND s.scheduled_date = ?',
                [$team['id'], $date]
            );
            $usedHrs = (float)($existing['hrs'] ?? 0);
            $maxHours = (float)\Env::get('MAX_DAILY_HOURS', 8);
            if (($usedHrs + $hours) <= $maxHours) {
                return $team;
            }
        }
        return null;
    }

    /** Create a draft schedule with ordered stops + ETAs. */
    private function createDraftSchedule(string $date, array $team, array $stops, float $depotLat, float $depotLng): ?string
    {
        try {
            $scheduleId = uuid();
            dbq(
                'INSERT INTO schedules (id, scheduled_date, team_id, status, total_distance_km)
                 VALUES (?, ?, ?, ?, ?)',
                [$scheduleId, $date, $team['id'], 'draft',
                 RouteOptimizer::totalDistance($stops, $depotLat, $depotLng)]
            );

            $etas = RouteOptimizer::estimateEtas($stops, '09:00:00', $depotLat, $depotLng);
            foreach ($stops as $i => $booking) {
                dbq(
                    'INSERT INTO schedule_stops (id, schedule_id, booking_id, stop_order, eta)
                     VALUES (?, ?, ?, ?, ?)',
                    [uuid(), $scheduleId, $booking['id'], $i + 1, $etas[$i] ?? null]
                );
                dbq(
                    'UPDATE bookings SET status = ?, schedule_id = ? WHERE id = ?',
                    ['scheduled', $scheduleId, $booking['id']]
                );
            }
            return $scheduleId;
        } catch (\Throwable $e) {
            error_log('SchedulingEngine: failed to create draft: ' . $e->getMessage());
            return null;
        }
    }
}