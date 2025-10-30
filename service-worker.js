const CACHE_NAME = 'forex-pwa-v1';
const APP_SHELL = [
  '/new_forex/',
  '/new_forex/index.php',
  '/new_forex/assets/css/style.css',
  '/new_forex/assets/js/app.js',
  '/new_forex/controllers/chartControllers.js',
  '/new_forex/controllers/updateController.js',
  '/new_forex/helpers/candles.php',
  '/new_forex/helpers/next_candle.php',
  '/new_forex/manifest.webmanifest',
  '/new_forex/offline.html'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL))
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => Promise.all(
      keys.filter((k) => k !== CACHE_NAME).map((k) => caches.delete(k))
    ))
  );
});

// Network-first for dynamic endpoints, cache-first for static assets
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  // Bypass non-GET
  if (event.request.method !== 'GET') return;

  const isApi = url.pathname.includes('/helpers/') || url.pathname.endsWith('trades.php');

  if (isApi) {
    event.respondWith(
      fetch(event.request).then((res) => {
        const resClone = res.clone();
        caches.open(CACHE_NAME).then((cache) => cache.put(event.request, resClone));
        return res;
      }).catch(() => caches.match(event.request))
    );
  } else {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        return cached || fetch(event.request).catch(() => caches.match('/new_forex/offline.html'));
      })
    );
  }
});
