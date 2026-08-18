// ── Glorikar — PWA + Web Push ─────────────────────────────
// Registers the service worker and manages the browser push
// subscription (VAPID). Subscription is opt-in via a dismissable
// banner; users can turn notifications off from the settings UI
// or by signing out.

import { VAPID_PUBLIC_KEY } from './config.js';

const SW_URL         = '/assets/js/sw.js';
const SUBSCRIBE_URL  = '/api/push/subscribe.php';
const UNSUBSCRIBE_URL = '/api/push/unsubscribe.php';
const KEY_SUBSCRIBED = 'push_subscribed';
const KEY_DECLINED   = 'push_declined';

/** Convert a base64url-encoded applicationServerKey into a Uint8Array. */
export function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
  const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
  const rawData = atob(base64);
  return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
}

function csrfToken() {
  const meta = document.querySelector('meta[name="csrf-token"]');
  return meta ? meta.content : '';
}

async function sendJson(url, method, body) {
  const res = await fetch(url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrfToken(),
    },
    body: JSON.stringify(body),
  });
  if (!res.ok) throw new Error(`Push request failed (${res.status})`);
  return res.json();
}

/** Register the service worker (no-op where unsupported). */
export async function registerServiceWorker() {
  if (!('serviceWorker' in navigator)) return null;
  try {
    const reg = await navigator.serviceWorker.register(SW_URL);
    console.log('[pwa] service worker registered', reg.scope);
    return reg;
  } catch (err) {
    console.warn('[pwa] service worker registration failed', err);
    return null;
  }
}

/** Current PushSubscription, or null when there is none. */
export async function getPushSubscription() {
  const reg = await swReady();
  if (!reg) return null;
  try {
    return reg.pushManager.getSubscription();
  } catch {
    return null;
  }
}

/** Active registration, resolving to null if the SW never becomes ready (3s). */
async function swReady() {
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) return null;
  return Promise.race([
    navigator.serviceWorker.ready,
    new Promise(resolve => setTimeout(() => resolve(null), 3000)),
  ]);
}

/** Subscribe (or reuse an existing subscription) and register it with the backend. */
export async function subscribeToPush() {
  if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
    throw new Error('Push not supported in this browser.');
  }
  const reg = await swReady();
  if (!reg) throw new Error('Service worker not ready.');

  const existing = await reg.pushManager.getSubscription();
  if (existing) {
    await sendJson(SUBSCRIBE_URL, 'POST', existing.toJSON());
    return existing;
  }

  const sub = await reg.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
  });
  await sendJson(SUBSCRIBE_URL, 'POST', sub.toJSON());
  return sub;
}

/** Opt out: unregister the subscription on this device + server. */
export async function turnOffNotifications() {
  localStorage.removeItem(KEY_SUBSCRIBED);
  localStorage.removeItem(KEY_DECLINED);

  const sub = await getPushSubscription();
  if (!sub) return;

  try {
    await sendJson(UNSUBSCRIBE_URL, 'DELETE', { endpoint: sub.endpoint });
  } catch (err) {
    console.warn('[pwa] unsubscribe request failed', err);
  }
  try {
    await sub.unsubscribe();
  } catch (err) {
    console.warn('[pwa] unsubscribe failed', err);
  }
}

// ── Opt-in banner ─────────────────────────────────────────
function showBanner() {
  if (document.getElementById('glorikar-push-banner')) return;

  const banner = document.createElement('div');
  banner.id = 'glorikar-push-banner';
  banner.style.cssText = [
    'position:fixed',
    'left:16px',
    'right:16px',
    'bottom:16px',
    'z-index:9999',
    'display:flex',
    'align-items:center',
    'gap:12px',
    'padding:14px 16px',
    'border-radius:12px',
    'background:#0F172A',
    'color:#E2E8F0',
    'box-shadow:0 8px 24px rgba(0,0,0,0.35)',
    'font:14px/1.45 system-ui,sans-serif',
  ].join(';');

  banner.innerHTML = `
    <div style="flex:1">Get notified about your service updates.</div>
    <button data-push-action="allow" style="border:0;border-radius:8px;padding:8px 14px;background:#0EA5E9;color:#fff;font-weight:600;cursor:pointer">Allow</button>
    <button data-push-action="dismiss" style="border:0;border-radius:8px;padding:8px 14px;background:#334155;color:#CBD5E1;cursor:pointer">Not now</button>`;

  document.body.appendChild(banner);

  banner.querySelector('[data-push-action="allow"]').addEventListener('click', async () => {
    try {
      const permission = await Notification.requestPermission();
      if (permission === 'granted') {
        await subscribeToPush();
        localStorage.setItem(KEY_SUBSCRIBED, '1');
      } else {
        localStorage.setItem(KEY_DECLINED, '1');
      }
    } catch (err) {
      console.warn('[pwa] subscribe failed', err);
    }
    banner.remove();
  });

  banner.querySelector('[data-push-action="dismiss"]').addEventListener('click', () => {
    localStorage.setItem(KEY_DECLINED, '1');
    banner.remove();
  });
}

// ── Init ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
  registerServiceWorker();

  // Only prompt on authenticated pages (no banner on login/register)
  if (!sessionStorage.getItem('glorikar_user')) return;
  if (!('Notification' in window)) return;
  if (localStorage.getItem(KEY_SUBSCRIBED) === '1') return;

  if (Notification.permission === 'granted') {
    try {
      await subscribeToPush();
      localStorage.setItem(KEY_SUBSCRIBED, '1');
    } catch (err) {
      console.warn('[pwa] sync subscription failed', err);
    }
  } else if (Notification.permission === 'default' && localStorage.getItem(KEY_DECLINED) !== '1') {
    showBanner();
  }
});
