<?php
/**
 * head.php — shared <head> partial.
 * Usage: $pageTitle = '...'; require __DIR__ . '/includes/head.php';
 */

require_once dirname(__DIR__, 2) . '/backend/includes/env.php';
require_once __DIR__ . '/helpers.php';

$pageTitle = $pageTitle ?? 'Glorikar Engineering';
$csrf = $_SESSION['csrf_token'] ?? csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($pageTitle) ?> · Glorikar Engineering</title>
  <meta name="description" content="Glorikar Engineering — professional aircon services. Cleaning, installation, relocation, repair, and inspection.">
  <meta name="theme-color" content="#0F172A">
  <meta name="api-url" content="<?= e(api_url()) ?>">
  <meta name="mapbox-token" content="<?= e(\Env::get('MAPBOX_ACCESS_TOKEN', '')) ?>">
  <meta name="csrf-token" content="<?= e($csrf) ?>">
  <link rel="manifest" href="manifest.json">
  <link rel="apple-touch-icon" href="assets/icons/icon-192.png">
  <link rel="icon" href="assets/icons/icon-192.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/theme.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/layout.css">
  <script src="assets/js/config.js" defer></script>
  <script src="assets/js/api.js" defer></script>
  <script src="assets/js/nav.js" defer></script>
</head>
<body>
<div class="app">