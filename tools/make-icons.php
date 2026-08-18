<?php
/**
 * tools/make-icons.php — generate PWA icons (PNG) without GD/Imagick.
 * Draws the Glorikar logo: navy background + arctic-blue "G" (ring with a gap + crossbar).
 * Usage: php tools/make-icons.php
 */

$outDir = __DIR__ . '/../frontend/assets/icons';
if (!is_dir($outDir)) {
    mkdir($outDir, 0777, true);
}

function makeIcon(int $size, array $bg, array $fg): string
{
    $px = [];

    // Fill background.
    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            $px[$y][$x] = [$bg[0], $bg[1], $bg[2], 255];
        }
    }

    $cx = $size / 2;
    $cy = $size / 2;
    $rOut = $size * 0.38;
    $rIn = $size * 0.24;

    // Anti-aliased donut + crossbar.
    for ($y = 0; $y < $size; $y++) {
        for ($x = 0; $x < $size; $x++) {
            $dx = $x + 0.5 - $cx;
            $dy = $y + 0.5 - $cy;
            $dist = sqrt($dx * $dx + $dy * $dy);
            $ang = atan2($dy, $dx); // -PI..PI

            $onRing = $dist <= $rOut && $dist >= $rIn;
            $gapOpen = $ang > -0.6 && $ang < 0.6; // right-side gap for the crossbar
            $onCrossbar = $x > $cx + $rIn - 2 && $x < $cx + $rOut + 2 && abs($y - $cy) < $size * 0.045;

            if ($onRing && !$gapOpen) {
                $px[$y][$x] = blend($px[$y][$x], $fg, ringAlpha($dist, $rIn, $rOut));
            } elseif ($onCrossbar) {
                $px[$y][$x] = [$fg[0], $fg[1], $fg[2], 255];
            }
        }
    }

    return encodePng($px);
}

function ringAlpha(float $d, float $rIn, float $rOut): float
{
    $edge = 1.5;
    $a = 1.0;
    if ($d < $rIn + $edge) $a = min(1, ($d - $rIn) / $edge);
    if ($d > $rOut - $edge) $a = min(1, ($rOut - $d) / $edge);
    return max(0, min(1, $a));
}

function blend(array $dst, array $src, float $a): array
{
    return [
        (int)round($dst[0] * (1 - $a) + $src[0] * $a),
        (int)round($dst[1] * (1 - $a) + $src[1] * $a),
        (int)round($dst[2] * (1 - $a) + $src[2] * $a),
        255,
    ];
}

function encodePng(array $px): string
{
    $size = count($px);

    $raw = '';
    for ($y = 0; $y < $size; $y++) {
        $raw .= "\x00"; // filter: none
        for ($x = 0; $x < $size; $x++) {
            [$r, $g, $b, $a] = $px[$y][$x];
            $raw .= chr($r) . chr($g) . chr($b) . chr($a);
        }
    }

    $ihdr = pack('NNCCCCC', $size, $size, 8, 6, 0, 0, 0); // width, height, bitdepth 8, RGBA, comp, filter, interlace
    $idat = zlib_encode($raw, ZLIB_ENCODING_DEFLATE);

    return "\x89PNG\r\n\x1a\n"
        . chunk('IHDR', $ihdr)
        . chunk('IDAT', $idat)
        . chunk('IEND', '');
}

function chunk(string $type, string $data): string
{
    $crc = crc32($type . $data);
    return pack('N', strlen($data)) . $type . $data . pack('N', $crc);
}

$navy = [15, 23, 42];   // #0F172A
$accent = [14, 165, 233]; // #0EA5E9

foreach ([192, 512] as $size) {
    $png = makeIcon($size, $navy, $accent);
    $path = $outDir . "/icon-{$size}.png";
    file_put_contents($path, $png);
    echo "wrote {$path} (" . strlen($png) . " bytes)\n";
}

// Favicon (32px) + apple touch (180px).
foreach ([32, 180] as $size) {
    $png = makeIcon($size, $navy, $accent);
    $path = $outDir . "/icon-{$size}.png";
    file_put_contents($path, $png);
    echo "wrote {$path} (" . strlen($png) . " bytes)\n";
}

echo "Done.\n";