<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — backend/services/GeoHelper.php
//  Pure-PHP geographic helpers used by the scheduling engine.
//  No external dependencies.
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

class GeoHelper
{
    private const EARTH_RADIUS_KM = 6371.0;

    /**
     * Great-circle distance between two coordinates (Haversine).
     */
    public static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Greedy nearest-neighbour route starting from the depot.
     * Each point must have coordinate keys — either 'lat'/'lng'
     * or 'latitude'/'longitude' (plus any extra fields, kept intact).
     * Returns the same points ordered by visit sequence.
     */
    public static function nearestNeighbour(array $points, float $depotLat, float $depotLng): array
    {
        $remaining = array_values($points);
        $route     = [];
        $curLat    = $depotLat;
        $curLng    = $depotLng;

        while (!empty($remaining)) {
            $bestIdx  = 0;
            $bestDist = PHP_FLOAT_MAX;

            foreach ($remaining as $i => $point) {
                $dist = self::distanceKm(
                    $curLat,
                    $curLng,
                    self::pointLat($point),
                    self::pointLng($point)
                );
                if ($dist < $bestDist) {
                    $bestDist = $dist;
                    $bestIdx  = $i;
                }
            }

            $route[] = $remaining[$bestIdx];
            $curLat  = self::pointLat($remaining[$bestIdx]);
            $curLng  = self::pointLng($remaining[$bestIdx]);
            unset($remaining[$bestIdx]);
            $remaining = array_values($remaining);
        }

        return $route;
    }

    private static function pointLat(array $point): float
    {
        return (float)($point['lat'] ?? $point['latitude'] ?? 0.0);
    }

    private static function pointLng(array $point): float
    {
        return (float)($point['lng'] ?? $point['longitude'] ?? 0.0);
    }

    /**
     * Radius-based geographic clustering.
     * Seeds with the first unassigned booking, pulls in every unassigned
     * booking within $radiusKm of any cluster member, repeats until the
     * cluster stops growing, then starts a new cluster.
     * Returns an array of clusters; each cluster is an array of booking rows.
     * Bookings use 'latitude'/'longitude' keys (DB row shape).
     */
    public static function cluster(array $bookings, float $radiusKm = 5.0): array
    {
        $unassigned = array_values($bookings);
        $clusters   = [];

        while (!empty($unassigned)) {
            $seed     = array_shift($unassigned);
            $cluster  = [$seed];

            $grew = true;
            while ($grew) {
                $grew = false;
                foreach ($unassigned as $i => $booking) {
                    foreach ($cluster as $member) {
                        $dist = self::distanceKm(
                            (float)$member['latitude'],
                            (float)$member['longitude'],
                            (float)$booking['latitude'],
                            (float)$booking['longitude']
                        );
                        if ($dist <= $radiusKm) {
                            $cluster[] = $booking;
                            unset($unassigned[$i]);
                            $grew = true;
                            break;
                        }
                    }
                }
                $unassigned = array_values($unassigned);
            }

            $clusters[] = $cluster;
        }

        return $clusters;
    }
}