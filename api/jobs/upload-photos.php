<?php
declare(strict_types=1);
// POST /api/jobs/upload-photos.php — admin only
// multipart/form-data: booking_id, type (before|after), photos[] files

require_once __DIR__ . '/../_bootstrap.php';
require_once __DIR__ . '/../../backend/includes/role-guard.php';



if ($_SERVER['REQUEST_METHOD'] !== 'POST') respond_error('Method not allowed', 405);
// CSRF via header (multipart can't use JSON body)
$token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
    respond_error('CSRF token mismatch', 403, 'CSRF_INVALID');
}

$bookingId = trim($_POST['booking_id'] ?? '');
$type      = trim($_POST['type']       ?? '');

if ($bookingId === '') respond_error('booking_id is required.', 422);
if (!in_array($type, ['before', 'after'], true)) respond_error('type must be "before" or "after".', 422);

$stmt = $pdo->prepare('SELECT id FROM bookings WHERE id = ? LIMIT 1');
$stmt->execute([$bookingId]);
if (!$stmt->fetch()) respond_error('Booking not found.', 404);

if (empty($_FILES['photos'])) respond_error('No files uploaded.', 422);

// Normalise $_FILES['photos'] into an array of individual files
$files = [];
if (is_array($_FILES['photos']['name'])) {
    foreach ($_FILES['photos']['name'] as $i => $name) {
        $files[] = [
            'name'     => $name,
            'tmp_name' => $_FILES['photos']['tmp_name'][$i],
            'size'     => $_FILES['photos']['size'][$i],
            'error'    => $_FILES['photos']['error'][$i],
        ];
    }
} else {
    $files[] = $_FILES['photos'];
}

$uploadDir = __DIR__ . '/../../uploads/job-photos/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// Block PHP execution in upload dir (write .htaccess once)
$htaccess = $uploadDir . '.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "php_flag engine off\nOptions -ExecCGI\n");
}

$maxBytes      = 5 * 1024 * 1024; // 5 MB
$allowedMimes  = ['image/jpeg', 'image/png', 'image/webp'];
$signatures    = [
    'jpeg' => "\xFF\xD8\xFF",
    'png'  => "\x89PNG",
    'webp' => 'RIFF',   // checked below with offset 8
];

$saved  = [];
$errors = [];

foreach ($files as $file) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Upload error on \"{$file['name']}\": code {$file['error']}";
        continue;
    }

    if ($file['size'] > $maxBytes) {
        $errors[] = "\"{$file['name']}\" exceeds 5 MB limit.";
        continue;
    }

    // MIME type check (finfo — server-side, not trusting browser MIME)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowedMimes, true)) {
        $errors[] = "\"{$file['name']}\" has invalid type ($mime). Only JPEG, PNG, WebP allowed.";
        continue;
    }

    // File signature check
    $header = file_get_contents($file['tmp_name'], false, null, 0, 12);
    $valid  = false;
    if (str_starts_with($header, "\xFF\xD8\xFF"))          $valid = true; // JPEG
    if (str_starts_with($header, "\x89PNG"))               $valid = true; // PNG
    if (str_starts_with($header, 'RIFF') && substr($header, 8, 4) === 'WEBP') $valid = true; // WebP

    if (!$valid) {
        $errors[] = "\"{$file['name']}\" failed signature check.";
        continue;
    }

    // Safe rename
    $ext      = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    };
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $dest     = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        $errors[] = "Failed to save \"{$file['name']}\".";
        continue;
    }

    // Relative URL stored in DB (served via a dedicated route or web server)
    $photoUrl = '/uploads/job-photos/' . $filename;

    $photoId = bin2hex(random_bytes(16));
    $pdo->prepare('
        INSERT INTO job_photos (id, booking_id, photo_url, type)
        VALUES (UUID(), ?, ?, ?)
    ')->execute([$bookingId, $photoUrl, $type]);

    $lastId = $pdo->prepare('SELECT id FROM job_photos WHERE booking_id = ? AND photo_url = ? LIMIT 1');
    $lastId->execute([$bookingId, $photoUrl]);
    $row = $lastId->fetch();

    $saved[] = ['id' => $row['id'], 'photo_url' => $photoUrl, 'type' => $type];
}

$response = ['photos' => $saved];
if (!empty($errors)) $response['errors'] = $errors;

respond($response, empty($saved) ? 400 : 201);