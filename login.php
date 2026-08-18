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
  <meta name="theme-color" content="#0EA5E9">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>Sign In — Glorikar Engineering</title>
  <link rel="stylesheet" href="assets/css/theme.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="stylesheet" href="assets/css/layout.css">
</head>
<body>

<div class="auth-layout">
  <div class="auth-card">

    <div class="auth-logo">
      <div class="auth-logo-mark">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 2a10 10 0 1 0 10 10"/><path d="M12 8v4l3 3"/>
          <path d="M22 12a10 10 0 0 0-3-7.1"/><path d="M17 2l5 5-5 5"/>
        </svg>
      </div>
      <div class="display-sm mt-sm">Glorikar Engineering</div>
      <div class="body-sm text-secondary mt-xs">Aircon Services Portal</div>
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
        <label class="form-label" for="email">Email address</label>
        <input class="input" type="email" id="email" name="email"
               autocomplete="email" placeholder="you@example.com" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input class="input" type="password" id="password" name="password"
               autocomplete="current-password" placeholder="••••••••" required>
      </div>

      <button class="btn btn-primary btn-full btn-lg" id="login-btn" type="button">
        <span id="btn-text">Sign in</span>
        <span id="btn-spinner" class="spinner hidden" style="width:16px;height:16px;border-width:2px;"></span>
      </button>
    </div>

    <p class="body-sm text-secondary mt-lg" style="text-align:center">
      Don't have an account? <a href="/register.php">Create one</a>
    </p>

  </div>
</div>

<script type="module">
import { login, getCachedUser } from './assets/js/auth.js';

// Already logged in → redirect
const existing = getCachedUser();
if (existing) {
  window.location.href = existing.role === 'admin'
    ? '/admin/dashboard.php'
    : '/client/home.php';
}

const emailEl  = document.getElementById('email');
const passEl   = document.getElementById('password');
const btn      = document.getElementById('login-btn');
const btnText  = document.getElementById('btn-text');
const spinner  = document.getElementById('btn-spinner');
const errBanner= document.getElementById('error-banner');

function setLoading(v) {
  btn.disabled = v;
  btnText.textContent = v ? 'Signing in…' : 'Sign in';
  spinner.classList.toggle('hidden', !v);
}

function showError(msg) {
  errBanner.textContent = msg;
  errBanner.classList.remove('hidden');
}

async function handleLogin() {
  errBanner.classList.add('hidden');
  const email    = emailEl.value.trim();
  const password = passEl.value;

  if (!email || !password) { showError('Please enter your email and password.'); return; }

  setLoading(true);
  try {
    const user = await login(email, password);
    window.location.href = user.role === 'admin'
      ? '/admin/dashboard.php'
      : '/client/home.php';
  } catch (err) {
    showError(err.message || 'Invalid email or password.');
    setLoading(false);
  }
}

btn.addEventListener('click', handleLogin);
passEl.addEventListener('keydown', e => { if (e.key === 'Enter') handleLogin(); });
</script>
</body>
</html>
