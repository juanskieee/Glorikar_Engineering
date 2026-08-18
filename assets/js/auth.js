// ── Glorikar — Auth Helpers ─────────────────────────────────
import { get, post } from './api.js';

const SESSION_KEY = 'glorikar_user';

/** Fetch current user from server and cache in sessionStorage */
export async function getMe() {
  try {
    const data = await get('/api/auth/me.php');
    sessionStorage.setItem(SESSION_KEY, JSON.stringify(data.user));
    return data.user;
  } catch {
    sessionStorage.removeItem(SESSION_KEY);
    return null;
  }
}

/** Return cached user (null if not logged in) */
export function getCachedUser() {
  try {
    return JSON.parse(sessionStorage.getItem(SESSION_KEY));
  } catch {
    return null;
  }
}

/** Redirect to login if not authenticated */
export async function requireAuth() {
  const user = await getMe();
  if (!user) {
    window.location.href = '/login.html';
    return null;
  }
  return user;
}

/** Redirect to login if not admin */
export async function requireAdmin() {
  const user = await requireAuth();
  if (user && user.role !== 'admin') {
    window.location.href = '/client/home.html';
    return null;
  }
  return user;
}

/** Login with email + password */
export async function login(email, password) {
  const data = await post('/api/auth/login.php', { email, password });
  sessionStorage.setItem(SESSION_KEY, JSON.stringify(data.user));
  return data.user;
}

/** Logout */
export async function logout() {
  try { await post('/api/auth/logout.php', {}); } catch { /* ignore */ }
  sessionStorage.removeItem(SESSION_KEY);
  window.location.href = '/login.html';
}
