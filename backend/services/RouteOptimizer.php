<?php
/**
 * RouteOptimizer.php — Step 3 of the scheduling engine.
 * Nearest-neighbor algorithm starting from the depot.
 */

namespace Glorikar\Services;

require_once __DIR__ . '/MapboxService.php';

class RouteOptimizer
{
    /**
     * Order bookings nearest-neighbor from the depot.
     *
     * @param array $bookings list of bookings with latitude/longitude
     * @param float $depotLat
     * @param float $depotLng
     * @return array ordered list of bookings
     */
    public static function optimize(array $bookings, float $depotLat, float $depotLng): array
    {
        if (count($bookings) <= 1) {
            return $bookings;
        }

        $remaining = $bookings;
        $route = [];
        $curLat = $depotLat;
        $curLng = $depotLng;

        while ($remaining) {
            $bestIdx = null;
            $bestDist = PHP_FLOAT_MAX;

            foreach ($remaining as $i => $b) {
                $dist = MapboxService::haversineKm(
                    $curLat, $curLng,
                    (float)$b['latitude'], (float)$b['longitude']
                );
                if ($dist < $bestDist) {
                    $bestDist = $dist;
                    $bestIdx = $i;
                }
            }

            $next = $remaining[$bestIdx];
            $route[] = $next;
            $curLat = (float)$next['latitude'];
            $curLng = (float)$next['longitude'];
            array_splice($remaining, $bestIdx, 1);
        }

        return $route;
    }

    /** Total route distance in km (Haversine chain, depot round-trip optional). */
    public static function totalDistance(array $route, float $depotLat, float $depotLng, bool $includeReturn = false): float
    {
        $total = 0.0;
        $prevLat = $depotLat;
        $prevLng = $depotLng;
        foreach ($route as $b) {
            $total += MapboxService::haversineKm($prevLat, $prevLng, (float)$b['latitude'], (float)$b['longitude']);
            $prevLat = (float)$b['latitude'];
            $prevLng = (float)$b['longitude'];
        }
        if ($includeReturn && $route) {
            $total += MapboxService::haversineKm($prevLat, $prevLng, $depotLat, $depotLng);
        }
        return round($total, 2);
    }

    /**
     * Estimate an ETA (TIME) for each stop given a start time and route.
     * Simple model: 30 min per stop + 45 km/h travel between stops.
     *
     * @param string $startTime "HH:MM:SS"
     * @return array<int,string|null> ETAs per stop index
     */
    public static function estimateEtas(array $route, string $startTime = '09:00:00', float $depotLat = 0, float $depotLng = 0): array
    {
        $minutes = self::timeToMinutes($startTime);
        $etas = [];
        $prevLat = $depotLat;
        $prevLng = $depotLng;

        foreach ($route as $b) {
            $travelKm = MapboxService::haversineKm($prevLat, $prevLng, (float)$b['latitude'], (float)$b['longitude']);
            $minutes += (int)round(($travelKm / 45) * 60); // 45 km/h travel
            $etas[] = self::minutesToTime($minutes);
            $minutes += 30; // 30 min per service stop
            $prevLat = (float)$b['latitude'];
            $prevLng = (float)$b['longitude'];
        }

        return $etas;
    }

    public static function timeToMinutes(string $time): int
    {
        [$h, $m, $s] = array_pad(explode(':', $time), 3, '0');
        return ((int)$h * 60) + (int)$m;
    }

    public static function minutesToTime(int $minutes): string
    {
        $minutes = max(0, $minutes);
        return sprintf('%02d:%02d:00', intdiv($minutes, 60), $minutes % 60);
    }
}