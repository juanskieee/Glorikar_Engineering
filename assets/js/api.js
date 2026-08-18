// ── Glorikar — API Fetch Wrapper ───────────────────────────
import { API_URL } from './config.js';

/**
 * Thin wrapper around fetch(). Attaches credentials (session cookie)
 * to every request and normalises error handling.
 *
 * @param {string} path   e.g. '/api/auth/me.php'
 * @param {object} [opts] standard fetch options (method, body, etc.)
 * @returns {Promise<any>} parsed JSON response
 * @throws  {Error} with message from server or HTTP status text
 */
function csrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta?.content || '';
}

export async function api(path, opts = {}) {
  const url = `${API_URL}${path}`;

  const method = (opts.method || 'GET').toUpperCase();
  const headers = {
    'Content-Type': 'application/json',
    'X-Requested-With': 'XMLHttpRequest',
    ...opts.headers,
  };

  // Attach the CSRF token to every state-changing request
  if (!['GET', 'HEAD', 'OPTIONS'].includes(method) && !headers['X-CSRF-Token']) {
    headers['X-CSRF-Token'] = csrfToken();
  }

  const defaults = {
    credentials: 'include',          // send session cookie cross-origin
    headers,
  };

  const res = await fetch(url, { ...defaults, ...opts });

  let data;
  try {
    data = await res.json();
  } catch {
    throw new Error(`Server error (${res.status})`);
  }

  if (!res.ok) {
    throw new Error(data?.message || `Request failed (${res.status})`);
  }

  return data;
}

export const get  = (path, opts = {}) => api(path, { method: 'GET',    ...opts });
export const post = (path, body, opts = {}) =>
  api(path, { method: 'POST',   body: JSON.stringify(body), ...opts });
export const patch = (path, body, opts = {}) =>
  api(path, { method: 'PATCH',  body: JSON.stringify(body), ...opts });
export const del  = (path, opts = {}) => api(path, { method: 'DELETE', ...opts });
