// ============================================================
// Glorikar Engineering — frontend config
// API_URL is injected by the server via <meta name="api-url">.
// ============================================================

window.GL_CONFIG = {
  API_URL: (document.querySelector('meta[name="api-url"]') || {}).content || 'http://localhost/glorikar_engineering/backend',
  MAPBOX_TOKEN: (document.querySelector('meta[name="mapbox-token"]') || {}).content || 'YOUR_MAPBOX_TOKEN',
  VAPID_PUBLIC_KEY: 'BL5NaICd5HHEKBhzyVy9uf09kWeoxlk3hdQRJpXn1pf3I6mChgBqGKUOSrjZk_Miv8lr-gzVRCWqEL4a7aCMZOQ'
};