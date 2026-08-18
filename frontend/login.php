<?php
/**
 * login.php â€” Login / Register (client role).
 */

require_once dirname(__DIR__) . '/backend/includes/env.php';
require_once __DIR__ . '/includes/helpers.php';
require_once dirname(__DIR__) . '/backend/includes/helpers.php';

start_session_secure();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Already logged in? Go straight to the right area.
if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? '/admin/dashboard.php' : '/client/home.php'));
    exit;
}

$pageTitle = 'Sign in';
$loginCsrf = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($pageTitle) ?> Â· Glorikar Engineering</title>
  <meta name="theme-color" content="#0F172A">
  <meta name="api-url" content="<?= e(api_url()) ?>">
  <meta name="csrf-token" content="<?= e($loginCsrf) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/theme.css">
  <link rel="stylesheet" href="/assets/css/components.css">
  <link rel="stylesheet" href="/assets/css/layout.css">
  <script src="/assets/js/config.js" defer></script>
  <script src="/assets/js/api.js" defer></script>
  <script src="/assets/js/auth.js" defer></script>
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">G</div>
    <h1 class="display-sm" id="auth-title">Welcome back</h1>
    <p class="body-sm text-secondary mb-md">Aircon cleaning, installation, repair &amp; more.</p>

    <?php if (isset($_GET['expired'])): ?>
      <div class="card mb-md" style="border-color:var(--status-cancelled);">
        <span class="body-sm" style="color:var(--status-cancelled);">Your session expired. Please sign in again.</span>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['reset'])): ?>
      <div class="card mb-md" style="border-color:var(--accent);">
        <span class="body-sm" style="color:var(--accent);">Password updated. Sign in with your new password.</span>
      </div>
    <?php endif; ?>

    <div class="auth-toggle">
      <button type="button" data-mode="login" class="active">Sign in</button>
      <button type="button" data-mode="register">Register</button>
    </div>

    <form id="auth-form" novalidate>
      <div class="field hidden" id="field-name">
        <label class="field-label label-sm text-secondary" for="full_name">Full name</label>
        <input class="input" type="text" id="full_name" name="full_name" autocomplete="name" placeholder="Juan Dela Cruz">
      </div>

      <div class="field">
        <label class="field-label label-sm text-secondary" for="email">Email</label>
        <input class="input" type="email" id="email" name="email" required autocomplete="email" placeholder="you@example.com">
      </div>

      <div class="field hidden" id="field-phone">
        <label class="field-label label-sm text-secondary" for="phone">Phone (optional)</label>
        <input class="input" type="tel" id="phone" name="phone" autocomplete="tel" placeholder="+63 912 345 6789">
      </div>

      <div class="field hidden" id="field-address">
        <label class="field-label label-sm text-secondary" for="address">Service address</label>
        <input class="input" type="text" id="address" name="address" autocomplete="street-address" placeholder="House / building address">
      </div>

      <div class="field">
        <label class="field-label label-sm text-secondary" for="password">Password</label>
        <input class="input" type="password" id="password" name="password" required autocomplete="current-password" placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢">
        <div class="field-error" id="pw-hint">At least 8 characters with letters and numbers.</div>
      </div>

      <div id="auth-errors" class="hidden" style="color:var(--status-cancelled);margin-bottom:var(--sp-md);font-size:13px;line-height:18px;"></div>

      <button class="btn btn-primary" type="submit">Sign in</button>
    </form>

    <p class="caption text-secondary mt-md" style="text-align:center;">
      <a href="forgot-password.php" style="color:var(--accent);text-decoration:none;">Forgot password?</a>
    </p>

    <p class="caption text-secondary mt-lg" style="text-align:center;">
      Aircon services on demand Â· Quezon City &amp; Metro Manila
    </p>
  </div>
</div>
</body>
</html>