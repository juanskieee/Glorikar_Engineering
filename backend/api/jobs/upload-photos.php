<?php
/**
 * POST /api/jobs/upload-photos.php — admin only. Before/after job photos.
 * Validates MIME + file signature, random UUID filenames, stored outside web root.
 */

require_once __DIR__ . '/../../includes/cors.php';
require_once __DIR__ . '/../../includes/role-guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_error('Method not allowed.', 405);
}

csrf_verify();

$bookingId = $_POST['booking_id'] ?? '';
$type = $_POST['type'] ?? 'after'; // before | after
if (!in_array($type, ['before', 'after'], true)) {
    json_error('Invalid photo type.', 422);
}
if (!preg_match('/^[0-9a-fA-F-]{36}$/', $bookingId)) {
    json_error('Not found.', 404);
}

$booking = dbfetch('SELECT id FROM bookings WHERE id = ?', [$bookingId]);
if (!$booking) {
    json_error('Not found.', 404);
}

$maxBytes = (int)Env::get('UPLOAD_MAX_BYTES', 5242880);
$uploadDir = realpath(__DIR__ . '/../../' . Env::get('UPLOAD_DIR', 'uploads'));

if (!$uploadDir) {
    json_error('Upload directory is not configured.', 500);
}

$saved = [];
$files = $_FILES['photos'] ?? null;
if (!$files || !is_array($files['name'])) {
    json_error('No photos uploaded.', 422);
}

foreach ($files['name'] as $i => $name) {
    if ($files['error'][$i] !== UPLOAD_ERR_OK) {
        continue;
    }
    if ($files['size'][$i] > $maxBytes) {
        json_error("Photo '{$name}' exceeds the size limit.", 422);
    }

    $tmp = $files['tmp_name'][$i];
    // Verify real file signature (not just extension/MIME).
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!in_array($mime, $allowedMimes, true)) {
        json_error("Photo '{$name}' is not a valid image (JPEG/PNG/WebP).", 422);
    }

    // Sanity check signature bytes too.
    $sig = file_get_contents($tmp, false, null, 0, 8);
    $isJpg = str_starts_with($sig, "\xFF\xD8\xFF");
    $isPng = str_starts_with($sig, "\x89PNG\r\n\x1a\n");
    $isWebp = substr($sig, 0, 4) === 'RIFF' && substr($sig, 8, 4) === 'WEBP';
    if (!$isJpg && !$isPng && !$isWebp) {
        json_error("Photo '{$name}' failed signature validation.", 422);
    }

    $ext = match ($mime) {
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'jpg',
    };
    $filename = uuid() . '.' . $ext;
    $dest = $uploadDir . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($tmp, $dest)) {
        json_error('Could not save photo. Please try again.', 500);
    }

    $photoUrl = 'uploads/' . $filename;
    dbq('INSERT INTO job_photos (id, booking_id, photo_url, type) VALUES (?, ?, ?, ?)', [
        uuid(), $bookingId, $photoUrl, $type
    ]);
    $saved[] = $photoUrl;
}

if (!$saved) {
    json_error('No photos were saved.', 422);
}

json_response(['ok' => true, 'photos' => $saved], 201);