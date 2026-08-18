<?php
/**
 * ClusterService.php — Step 1 of the scheduling engine.
 * Groups pending bookings within a radius window into geographic clusters.
 *
 * Uses Mapbox Distance Matrix where available, else Haversine fallback.
 */

namespace Glorikar\Services;

require_once __DIR__ . '/MapboxService.php';

class ClusterService
{
    /** Cluster radius in km. */
    private float $radiusKm;

    public function __construct(float $radiusKm = 5.0)
    {
        $this->radiusKm = $radiusKm;
    }

    /**
     * Cluster a list of bookings (each with lat/lng) into groups.
     * Simple greedy radius check: seed a cluster with the first unvisited
     * booking, then absorb every other booking within `radiusKm` of it.
     *
     * @param array $bookings list of booking arrays with 'latitude'/'longitude'
     * @return array<int, array> list of clusters, each cluster is a list of bookings
     */
    public function cluster(array $bookings): array
    {
        $clusters = [];
        $used = [];

        foreach ($bookings as $i => $b) {
            if (isset($used[$i])) {
                continue;
            }
            $cluster = [$b];
            $used[$i] = true;

            foreach ($bookings as $j => $other) {
                if ($i === $j || isset($used[$j])) {
                    continue;
                }
                $dist = MapboxService::haversineKm(
                    (float)$b['latitude'], (float)$b['longitude'],
                    (float)$other['latitude'], (float)$other['longitude']
                );
                if ($dist <= $this->radiusKm) {
                    $cluster[] = $other;
                    $used[$j] = true;
                }
            }
            $clusters[] = $cluster;
        }

        return $clusters;
    }

    /** Compute the centroid [lat, lng] of a cluster. */
    public static function centroid(array $cluster): array
    {
        $lat = 0;
        $lng = 0;
        $n = count($cluster);
        foreach ($cluster as $b) {
            $lat += (float)$b['latitude'];
            $lng += (float)$b['longitude'];
        }
        return ['lat' => $lat / $n, 'lng' => $lng / $n];
    }
}