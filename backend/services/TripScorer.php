<?php
/**
 * TripScorer.php — Step 2 of the scheduling engine.
 *
 * trip_score = (total_services_count * 10) - (distance_from_depot_km * 2)
 *
 * - A far client with 1 service may score 8 -> attach to a nearby route.
 * - A far client with 4 services scores 20 -> can anchor their own route.
 * - Bookings below MIN_TRIP_SCORE are deferred until a nearby route exists.
 */

namespace Glorikar\Services;

class TripScorer
{
    public const MIN_TRIP_SCORE = 10;

    public static function minScore(): int
    {
        return (int)\Env::get('MIN_TRIP_SCORE', self::MIN_TRIP_SCORE);
    }

    /**
     * Compute the trip score for a single booking.
     *
     * @param array $booking booking row (must include latitude/longitude)
     * @param int   $totalServicesCount summed quantity across booking_services
     * @param float $distanceFromDepotKm distance to depot
     */
    public static function score(array $booking, int $totalServicesCount, float $distanceFromDepotKm): float
    {
        $score = ($totalServicesCount * 10) - ($distanceFromDepotKm * 2);
        return round($score, 2);
    }

    /** True if the booking is strong enough to anchor its own route. */
    public static function isAnchorable(float $score): bool
    {
        return $score >= self::minScore();
    }
}