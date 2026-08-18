<?php
$files = [
    'icon-32.png' => 'frontend/assets/icons/icon-32.png',
    'icon-180.png' => 'frontend/assets/icons/icon-180.png',
    'icon-192.png' => 'frontend/assets/icons/icon-192.png',
    'icon-512.png' => 'frontend/assets/icons/icon-512.png',
];
foreach ($files as $label => $rel) {
    $data = file_get_contents(__DIR__ . '/../' . $rel);
    echo "== $label (".strlen($data)." bytes) ==\n";
    if (substr($data, 0, 8) !== "\x89PNG\r\n\x1a\n") { echo "  BAD SIGNATURE\n"; continue; }
    $pos = 8;
    $chunks = 0;
    while ($pos + 12 <= strlen($data)) {
        $len = unpack('N', substr($data, $pos, 4))[1];
        $type = substr($data, $pos + 4, 4);
        $crc = unpack('N', substr($data, $pos + 8 + $len, 4))[1];
        $calc = crc32(substr($data, $pos + 4, 4 + $len));
        $ok = $calc === $crc ? 'OK' : 'CRC-FAIL';
        echo "  chunk $type len=$len crc=$ok";
        if ($type === 'IHDR' && $len >= 8) {
            $w = unpack('N', substr($data, $pos + 8, 4))[1];
            $h = unpack('N', substr($data, $pos + 12, 4))[1];
            $depth = ord($data[$pos + 16]);
            $color = ord($data[$pos + 17]);
            echo "  $w x $h depth=$depth colortype=$color";
        }
        echo "\n";
        if ($type === 'IEND') break;
        $pos += 12 + $len;
        $chunks++;
        if ($chunks > 20) { echo "  ...stopped\n"; break; }
    }
}
