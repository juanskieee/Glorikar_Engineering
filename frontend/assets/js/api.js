// ============================================================
// api.js — fetch wrapper for the Glorikar backend.
// - Always sends credentials (session cookie).
// - Attaches CSRF token from <meta name="csrf-token"> on writes.
// - Normalizes errors into thrown { status, message } objects.
// ============================================================

window.GL = window.GL || {};

GL.api = async function (path, opts = {}) {
  const { method = 'GET', body, headers = {} } = opts;
  const metaCsrf = document.querySelector('meta[name="csrf-token"]');

  const fetchOpts = {
    method,
    credentials: 'same-origin',
    headers: { ...headers },
  };

  if (body !== undefined) {
    if (body instanceof FormData) {
      fetchOpts.body = body;
    } else {
      fetchOpts.headers['Content-Type'] = 'application/json';
      fetchOpts.body = JSON.stringify(body);
    }
  }

  if (!['GET', 'HEAD'].includes(method) && metaCsrf) {
    fetchOpts.headers['X-CSRF-Token'] = metaCsrf.content;
  }

  let res;
  try {
    res = await fetch(window.GL_CONFIG.API_URL + path, fetchOpts);
  } catch (e) {
    throw { status: 0, message: 'Network error. Please check your connection.' };
  }

  let data = null;
  try {
    data = await res.json();
  } catch (e) {
    /* non-JSON response */
  }

  if (!res.ok) {
    const message = (data && data.error) ? data.error : `Request failed (${res.status}).`;
    if (res.status === 401 && !path.startsWith('/api/auth/login')) {
      // Session expired — bounce to login.
      const href = new URL(window.location.href);
      href.search = '';
      window.location.href = href.origin + href.pathname.replace(/[^/]*$/, '') + 'login.php?expired=1';
    }
    throw { status: res.status, message, data };
  }

  return data;
};

GL.get = (path) => GL.api(path);
GL.post = (path, body) => GL.api(path, { method: 'POST', body });
GL.patch = (path, body) => GL.api(path, { method: 'PATCH', body });

/** Small toast helper. */
GL.toast = function (message, ms = 2600) {
  let el = document.querySelector('.toast');
  if (!el) {
    el = document.createElement('div');
    el.className = 'toast';
    document.body.appendChild(el);
  }
  el.textContent = message;
  el.classList.add('visible');
  clearTimeout(GL.toast._t);
  GL.toast._t = setTimeout(() => el.classList.remove('visible'), ms);
};

/** Format a PHP peso amount. */
GL.money = (n) => '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

/** Human status label. */
GL.statusLabel = (s) => (s || '').replace(/_/g, ' ');