<?php
/**
 * MapboxService.php — Mapbox GL API wrapper (Geocoding + Distance Matrix).
 * Uses cURL directly so no composer dependency is required.
 *
 * NOTE: Distance Matrix limit is 25x25 per request — batch in PHP.
 */

namespace Glorikar\Services;

class MapboxService
{
    public static function token(): string
    {
        return \Env::get('MAPBOX_ACCESS_TOKEN', '');
    }

    public static function configured(): bool
    {
        return self::token() !== '';
    }

    /** cURL GET helper. Returns decoded JSON array or null. */
    private static function get(string $url): ?array
    {
        if (!self::configured()) {
            return null;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER     => ['User-Agent: GlorikarEngineering/1.0'],
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($body === false || $code >= 400) {
            error_log("Mapbox API error (HTTP {$code}): " . ($body ?: curl_error($ch)));
            return null;
        }
        return json_decode($body, true);
    }

    /**
     * Geocode a free-text address -> [lat, lng] or null.
     */
    public static function geocode(string $address): ?array
    {
        $url = 'https://api.mapbox.com/geocoding/v5/mapbox.places/'
            . rawurlencode($address)
            . '.json?limit=1&access_token=' . rawurlencode(self::token());
        $res = self::get($url);
        if (!$res || empty($res['features'])) {
            return null;
        }
        $center = $res['features'][0]['center'] ?? null;
        if (!$center) {
            return null;
        }
        return ['lng' => (float)$center[0], 'lat' => (float)$center[1]];
    }

    /**
     * Driving distances (km) between an origin point and destination points.
     *
     * @param array $origin [lat, lng]
     * @param array $destinations [[lat, lng], ...] max 24
     * @return array|null list of distances in km matching $destinations, or null on failure
     */
    public static function distanceMatrix(array $origin, array $destinations): ?array
    {
        if (!$destinations) {
            return [];
        }
        if (count($destinations) > 24) {
            $destinations = array_slice($destinations, 0, 24);
        }

        $coords = implode(';', array_map(
            fn($d) => $d[1] . ',' . $d[0],
            array_merge([$origin], $destinations)
        ));
        $url = 'https://api.mapbox.com/directions-matrix/v1/mapbox/driving/'
            . $coords
            . '?annotations=distance&access_token=' . rawurlencode(self::token());

        $res = self::get($url);
        if (!$res || empty($res['distances'][0])) {
            return null;
        }
        $distances = [];
        foreach ($res['distances'][0] as $meters) {
            $distances[] = $meters / 1000.0; // meters -> km
        }
        array_shift($distances); // remove the origin->origin 0 entry
        return $distances;
    }

    /**
     * Driving distance (km) between two points.
     */
    public static function distance(float $lat1, float $lng1, float $lat2, float $lng2): ?float
    {
        $d = self::distanceMatrix([$lat1, $lng1], [[$lat2, $lng2]]);
        return $d ? $d[0] : null;
    }

    /** Haversine distance in km (fallback when Mapbox unavailable). */
    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earth = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earth * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}