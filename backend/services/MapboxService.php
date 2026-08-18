<?php
// ═══════════════════════════════════════════════════════════
//  GLORIKAR — backend/services/MapboxService.php
//  Static helpers around the Mapbox Web APIs.
//  Reads MAPBOX_ACCESS_TOKEN from $_ENV.
// ═══════════════════════════════════════════════════════════

declare(strict_types=1);

class MapboxService
{
    private const BASE_URL = 'https://api.mapbox.com';

    private static function token(): string
    {
        $token = $_ENV['MAPBOX_ACCESS_TOKEN'] ?? '';
        if ($token === '' || str_starts_with($token, 'pk.eyJ1Ijoi')) {
            throw new RuntimeException('MAPBOX_ACCESS_TOKEN is not configured.');
        }
        return $token;
    }

    private static function get(string $url): array
    {
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'ignore_errors' => true,
                'user_agent' => 'Glorikar/1.0',
            ],
        ]);

        $raw = @file_get_contents($url, false, $context);
        if ($raw === false) {
            throw new RuntimeException('Mapbox request failed.');
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new RuntimeException('Mapbox returned an invalid response.');
        }

        if (isset($data['message']) && isset($data['code'])) {
            throw new RuntimeException('Mapbox error: ' . $data['message']);
        }

        return $data;
    }

    /**
     * Geocode a free-text address into coordinates.
     *
     * @return array{lat: float, lng: float}
     * @throws RuntimeException
     */
    public static function geocode(string $address): array
    {
        $address = trim($address);
        if ($address === '') throw new RuntimeException('Address is empty.');

        $url = self::BASE_URL
            . '/geocoding/v5/mapbox.places/'
            . rawurlencode($address)
            . '.json?limit=1&access_token=' . self::token();

        $data = self::get($url);

        $feature = $data['features'][0] ?? null;
        if (!$feature || !isset($feature['center'][0], $feature['center'][1])) {
            throw new RuntimeException('No result for address: ' . $address);
        }

        return [
            'lat' => (float)$feature['center'][1],
            'lng' => (float)$feature['center'][0],
        ];
    }

    /**
     * Driving travel-time matrix (seconds) between origins and destinations.
     * Mapbox caps requests at 25×25, so larger matrices are split into
     * 25-row batches to stay under the limit.
     *
     * @param array $origins       List of ['lat'=>, 'lng'=>] (or ['latitude'=>,'longitude'=>])
     * @param array $destinations  Same shape as $origins
     * @return array 2D array [origin][destination] = seconds (null where impossible)
     * @throws RuntimeException
     */
    public static function distanceMatrix(array $origins, array $destinations): array
    {
        $project = static function (array $pt): array {
            $lat = $pt['lat'] ?? $pt['latitude'] ?? null;
            $lng = $pt['lng'] ?? $pt['longitude'] ?? null;
            if ($lat === null || $lng === null) {
                throw new RuntimeException('Each point needs lat/lng.');
            }
            return [$lat, $lng];
        };

        $o = array_map($project, $origins);
        $d = array_map($project, $destinations);

        if (count($o) === 0 || count($d) === 0) {
            return [];
        }

        // Mapbox allows max 25 coordinates per request
        $maxPerRequest = 25;
        $matrix = [];

        foreach (array_chunk($o, $maxPerRequest) as $originChunk) {
            $row = [];

            foreach (array_chunk($d, $maxPerRequest) as $destChunk) {
                $sources = implode(';', array_map(static fn($p) => $p[0] . ',' . $p[1], $originChunk));
                $targets = implode(';', array_map(static fn($p) => $p[0] . ',' . $p[1], $destChunk));

                $url = self::BASE_URL
                    . '/directions-matrix/v1/mapbox/driving/'
                    . $sources
                    . '?annotations=duration&destinations=' . $targets
                    . '&access_token=' . self::token();

                $data = self::get($url);

                if (!isset($data['durations']) || !is_array($data['durations'])) {
                    throw new RuntimeException('Mapbox matrix returned no durations.');
                }

                // Append durations for this destination chunk onto each row
                foreach ($data['durations'] as $i => $durations) {
                    $row[$i] = array_merge($row[$i] ?? [], array_map(
                        static fn($sec) => $sec === null ? null : (int)$sec,
                        $durations
                    ));
                }
            }

            $matrix = array_merge($matrix, $row);
        }

        return $matrix;
    }
}