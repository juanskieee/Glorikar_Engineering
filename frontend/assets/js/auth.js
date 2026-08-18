// ============================================================
// auth.js — login / register form handling.
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('auth-form');
  if (!form) return;

  const modeTabs = document.querySelectorAll('.auth-toggle button');
  const titleEl = document.getElementById('auth-title');
  const nameField = document.getElementById('field-name');
  const phoneField = document.getElementById('field-phone');
  const addressField = document.getElementById('field-address');
  const submitBtn = form.querySelector('button[type="submit"]');
  const errorsEl = document.getElementById('auth-errors');

  let mode = 'login';

  function setMode(next) {
    mode = next;
    modeTabs.forEach((t) => t.classList.toggle('active', t.dataset.mode === mode));
    const isLogin = mode === 'login';
    if (titleEl) titleEl.textContent = isLogin ? 'Welcome back' : 'Create your account';
    if (nameField) nameField.classList.toggle('hidden', isLogin);
    if (phoneField) phoneField.classList.toggle('hidden', isLogin);
    if (addressField) addressField.classList.toggle('hidden', isLogin);
    if (submitBtn) submitBtn.textContent = isLogin ? 'Sign in' : 'Create account';
    if (errorsEl) errorsEl.textContent = '';
    form.querySelectorAll('.field-error').forEach((f) => f.classList.remove('visible'));
  }

  modeTabs.forEach((t) => t.addEventListener('click', () => setMode(t.dataset.mode)));

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Please wait...'; }
    if (errorsEl) errorsEl.textContent = '';

    const base = {
      email: form.email.value.trim(),
      password: form.password.value,
    };

    try {
      let res;
      if (mode === 'login') {
        res = await GL.post('/api/auth/login.php', base);
      } else {
        res = await GL.post('/api/auth/register.php', {
          ...base,
          full_name: form.full_name.value.trim(),
          phone: form.phone.value.trim(),
          address: form.address.value.trim(),
        });
      }
      const role = res.user && res.user.role;
      // Keep the CSRF meta in sync (server rotates the token on login/register).
      if (res.csrf_token) {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) meta.content = res.csrf_token;
      }
      window.location.href = role === 'admin' ? 'admin/dashboard.php' : 'client/home.php';
    } catch (err) {
      if (errorsEl) {
        errorsEl.textContent = err.message;
        errorsEl.classList.remove('hidden');
      } else {
        GL.toast(err.message);
      }
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = mode === 'login' ? 'Sign in' : 'Create account'; }
    }
  });
});