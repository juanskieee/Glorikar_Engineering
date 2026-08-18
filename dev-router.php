<?php
/**
 * dev-router.php — local development router for PHP's built-in server.
 *
 *   php -S localhost:8000 dev-router.php
 *
 * Routes:
 *   /api/*     -> backend/api/*
 *   /uploads/* -> backend/uploads/*
 *   everything else -> frontend/*
 *
 * Production deploy uses Apache/Nginx with the two roots served separately
 * (see GLORIKAR_SPEC_WEB.md and frontend/.htaccess.example).
 */

$uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$root = __DIR__;

$mimeByExt = [
    'css'  => 'text/css',
    'js'   => 'application/javascript',
    'json' => 'application/json',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'webp' => 'image/webp',
    'svg'  => 'image/svg+xml',
    'ico'  => 'image/x-icon',
    'html' => 'text/html',
];

/** Serve a static file with the right MIME type. */
function dev_static(string $file, array $mimeByExt): void
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    header('Content-Type: ' . ($mimeByExt[$ext] ?? 'application/octet-stream'));
    readfile($file);
    exit;
}

// --- API ---------------------------------------------------------------
if (preg_match('#^/api(/.*)?$#', $uri, $m)) {
    $target = $root . '/backend/api' . ($m[1] ?? '');
    if (is_dir($target)) {
        $target = rtrim($target, '/') . '/index.php';
    }
    if (is_file($target)) {
        require $target;
        exit;
    }
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Endpoint not found.']);
    exit;
}

// --- Uploaded photos -----------------------------------------------------
if (str_starts_with($uri, '/uploads/')) {
    $file = $root . '/backend' . $uri;
    if (is_file($file)) {
        dev_static($file, $mimeByExt);
    }
    http_response_code(404);
    exit('Not found');
}

// --- Frontend ------------------------------------------------------------
if ($uri === '/' || $uri === '') {
    require $root . '/frontend/index.php';
    exit;
}

$target = $root . '/frontend' . $uri;
if (is_dir($target)) {
    $target = rtrim($target, '/') . '/index.php';
}

if (is_file($target)) {
    if (strtolower(pathinfo($target, PATHINFO_EXTENSION)) === 'php') {
        require $target;
    } else {
        dev_static($target, $mimeByExt);
    }
    exit;
}

// Unknown path -> entry point handles redirect.
require $root . '/frontend/index.php';