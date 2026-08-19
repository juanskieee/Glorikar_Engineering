<?php
require __DIR__ . '/backend/includes/csrf.php';
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
$csrfToken = get_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
  <link rel="manifest" href="/manifest.json">
  <link rel="icon" type="image/x-icon" href="/assets/icons/glorikar_logo.ico">
  <link rel="apple-touch-icon" href="/assets/glorikar_logo.png">
  <meta name="theme-color" content="#0EA5E9">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>Create Account — Glorikar Engineering</title>
  <link rel="stylesheet" href="assets/css/theme.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/layout.css">
</head>
<body>

<div class="auth-layout">
  <div class="auth-card">

    <div class="auth-logo">
      <div class="auth-logo-mark" style="width: 48px; height: 48px; background: transparent; border-radius: var(--r-md); overflow: hidden;">
        <img src="/assets/glorikar_logo.png" alt="Glorikar Engineering Logo" style="width: 100%; height: 100%; object-fit: cover;">
      </div>
      <div class="display-sm mt-sm">Create Account</div>
      <div class="body-sm text-secondary mt-xs">Glorikar Engineering Client Portal</div>
    </div>

    <div id="error-banner" class="hidden" style="
      background: rgba(239,68,68,0.1);
      border: 1px solid var(--status-cancelled);
      border-radius: var(--r-sm);
      padding: var(--sp-sm) var(--sp-md);
      font: 400 13px/18px Inter;
      color: var(--status-cancelled);
      margin-bottom: var(--sp-md);
    "></div>

    <div class="stack stack-md">
      <div class="form-group">
        <label class="form-label" for="full_name">Full name</label>
        <input class="input" type="text" id="full_name" name="full_name"
               autocomplete="name" placeholder="Maria Santos" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Email address</label>
        <input class="input" type="email" id="email" name="email"
               autocomplete="email" placeholder="you@example.com" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="phone">Phone number</label>
        <input class="input" type="tel" id="phone" name="phone"
               autocomplete="tel" placeholder="+63 9XX XXX XXXX">
      </div>

      <div class="form-group">
        <label class="form-label" for="address">Service address</label>
        <input class="input" type="text" id="address" name="address"
               autocomplete="street-address"
               placeholder="Unit, building, street, city">
        <span class="caption text-secondary">We use this to schedule your technician visits.</span>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="input" type="password" id="password" name="password"
               autocomplete="new-password" placeholder="At least 8 characters" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password_confirm">Confirm password</label>
        <input class="input" type="password" id="password_confirm" name="password_confirm"
               autocomplete="new-password" placeholder="Repeat password" required>
      </div>

      <button class="btn btn-primary btn-full btn-lg" id="register-btn" type="button">
        <span id="btn-text">Create account</span>
        <span id="btn-spinner" class="spinner hidden" style="width:16px;height:16px;border-width:2px;"></span>
      </button>
    </div>

    <p class="body-sm text-secondary mt-lg" style="text-align:center">
      Already have an account? <a href="/login.php">Sign in</a>
    </p>

  </div>
</div>

<script type="module">
import { post } from './assets/js/api.js';
import './assets/js/pwa.js';

const btn       = document.getElementById('register-btn');
const btnText   = document.getElementById('btn-text');
const spinner   = document.getElementById('btn-spinner');
const errBanner = document.getElementById('error-banner');

function setLoading(v) {
  btn.disabled = v;
  btnText.textContent = v ? 'Creating account…' : 'Create account';
  spinner.classList.toggle('hidden', !v);
}

function showError(msg) {
  errBanner.textContent = msg;
  errBanner.classList.remove('hidden');
  errBanner.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

btn.addEventListener('click', async () => {
  errBanner.classList.add('hidden');

  const fullName  = document.getElementById('full_name').value.trim();
  const email     = document.getElementById('email').value.trim();
  const phone     = document.getElementById('phone').value.trim();
  const address   = document.getElementById('address').value.trim();
  const password  = document.getElementById('password').value;
  const confirm   = document.getElementById('password_confirm').value;

  if (!fullName || !email || !address || !password) {
    showError('Please fill in all required fields.'); return;
  }
  if (password.length < 8) {
    showError('Password must be at least 8 characters.'); return;
  }
  if (password !== confirm) {
    showError('Passwords do not match.'); return;
  }

  setLoading(true);
  try {
    await post('/api/auth/register.php', { full_name: fullName, email, phone, address, password });
    window.location.href = '/login.php?registered=1';
  } catch (err) {
    showError(err.message || 'Registration failed. Please try again.');
    setLoading(false);
  }
});
</script>
</body>
</html>
