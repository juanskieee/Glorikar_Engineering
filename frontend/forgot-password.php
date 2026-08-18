<?php
/**
 * forgot-password.php — request a password reset email.
 */

require_once dirname(__DIR__) . '/backend/includes/env.php';
require_once __DIR__ . '/includes/helpers.php';
require_once dirname(__DIR__) . '/backend/includes/helpers.php';

start_session_secure();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!empty($_SESSION['user_id'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'client/home.php'));
    exit;
}

$pageTitle = 'Forgot password';
$pageCsrf = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($pageTitle) ?> · Glorikar Engineering</title>
  <meta name="theme-color" content="#0F172A">
  <meta name="api-url" content="<?= e(api_url()) ?>">
  <meta name="csrf-token" content="<?= e($pageCsrf) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/theme.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/layout.css">
  <script src="assets/js/config.js" defer></script>
  <script src="assets/js/api.js" defer></script>
  <script src="assets/js/auth.js" defer></script>
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <a href="login.php" class="caption text-secondary" style="display:block;margin-bottom:16px;">&larr; Back to sign in</a>
    <div class="auth-logo">G</div>
    <h1 class="display-sm">Forgot password</h1>
    <p class="body-sm text-secondary mb-md">Enter your account email and we'll send you a reset link.</p>

    <form id="forgot-form" novalidate>
      <div class="field">
        <label class="field-label label-sm text-secondary" for="email">Email</label>
        <input class="input" type="email" id="email" name="email" required autocomplete="email" placeholder="you@example.com">
      </div>

      <div id="forgot-msg" class="hidden" style="color:var(--status-cancelled);margin-bottom:var(--sp-md);font-size:13px;line-height:18px;"></div>

      <button class="btn btn-primary" type="submit">Send reset link</button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('forgot-form');
  if (!form) return;
  const btn = form.querySelector('button[type="submit"]');
  const msg = document.getElementById('forgot-msg');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (btn) { btn.disabled = true; btn.textContent = 'Sending...'; }
    if (msg) { msg.classList.add('hidden'); msg.textContent = ''; }

    try {
      await GL.post('/api/auth/forgot-password.php', { email: form.email.value.trim() });
      // Same generic success either way — never reveals whether the account exists.
      msg.textContent = 'If that email is registered, a reset link has been sent. Check your inbox (and spam folder).';
      msg.style.color = 'var(--accent)';
      msg.classList.remove('hidden');
    } catch (err) {
      if (msg) {
        msg.style.color = 'var(--status-cancelled)';
        msg.textContent = err.message;
        msg.classList.remove('hidden');
      }
    } finally {
      if (btn) { btn.disabled = false; btn.textContent = 'Send reset link'; }
    }
  });
});
</script>
</body>
</html>