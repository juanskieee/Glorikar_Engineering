<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — backend/services/TripScorer.php
//  Calculates trip_score for a booking.
//  Formula: (total_qty × 10) - (distance_from_depot_km × 2)
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

require_once __DIR__ . '/GeoHelper.php';

class TripScorer
{
    public function __construct(
        private readonly float $depotLat,
        private readonly float $depotLng,
        private readonly float $minScore
    ) {}

    /**
     * Score a single booking.
     * $booking must have: latitude, longitude, total_qty (sum of booking_services.quantity)
     */
    public function score(array $booking): float
    {
        $lat = $booking['latitude']  !== null ? (float)$booking['latitude']  : $this->depotLat;
        $lng = $booking['longitude'] !== null ? (float)$booking['longitude'] : $this->depotLng;

        $distKm    = GeoHelper::distanceKm($this->depotLat, $this->depotLng, $lat, $lng);
        $totalQty  = (int)($booking['total_qty'] ?? 1);

        return ($totalQty * 10) - ($distKm * 2);
    }

    public function meetsThreshold(float $score): bool
    {
        return $score >= $this->minScore;
    }
}