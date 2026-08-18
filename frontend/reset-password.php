<?php
/**
 * reset-password.php?token=xxx — choose a new password.
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

$token = trim($_GET['token'] ?? '');
$hasToken = $token !== '';
$pageCsrf = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= $hasToken ? 'Reset password' : 'Invalid link' ?> · Glorikar Engineering</title>
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
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <a href="login.php" class="caption text-secondary" style="display:block;margin-bottom:16px;">&larr; Back to sign in</a>
    <div class="auth-logo">G</div>

    <?php if ($hasToken): ?>
      <h1 class="display-sm">Set a new password</h1>
      <p class="body-sm text-secondary mb-md">Choose a strong password for your account.</p>

      <form id="reset-form" novalidate>
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="field">
          <label class="field-label label-sm text-secondary" for="password">New password</label>
          <input class="input" type="password" id="password" name="password" required autocomplete="new-password" placeholder="At least 8 characters, letters + numbers">
        </div>
        <div class="field-error" id="pw-hint">At least 8 characters with letters and numbers.</div>

        <div id="reset-msg" class="hidden" style="color:var(--status-cancelled);margin-bottom:var(--sp-md);font-size:13px;line-height:18px;"></div>

        <button class="btn btn-primary" type="submit">Update password</button>
      </form>
    <?php else: ?>
      <h1 class="display-sm">Invalid reset link</h1>
      <p class="body-sm text-secondary mb-md">This link is missing its token. Request a new password reset to get a fresh link.</p>
      <a class="btn btn-primary" href="forgot-password.php" style="text-align:center;">Request a new link</a>
    <?php endif; ?>
  </div>
</div>

<?php if ($hasToken): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('reset-form');
  if (!form) return;
  const btn = form.querySelector('button[type="submit"]');
  const msg = document.getElementById('reset-msg');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (btn) { btn.disabled = true; btn.textContent = 'Updating...'; }
    if (msg) { msg.classList.add('hidden'); msg.textContent = ''; }

    try {
      await GL.post('/api/auth/reset-password.php', {
        token: form.token.value,
        password: form.password.value,
      });
      window.location.href = 'login.php?reset=1';
    } catch (err) {
      if (msg) {
        msg.textContent = err.message;
        msg.classList.remove('hidden');
      }
      if (btn) { btn.disabled = false; btn.textContent = 'Update password'; }
    }
  });
});
</script>
<?php endif; ?>
</body>
</html>