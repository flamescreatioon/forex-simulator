// Service Worker with dynamic path detection
const VERSION = 'forex-pwa-v3';
const CACHE_NAME = `${VERSION}-${self.location.pathname.split('/').filter(Boolean)[0] || 'root'}`;

// Detect base path from service worker location
const getBasePath = () => {
    const swPath = self.location.pathname;
    const basePath = swPath.substring(0, swPath.lastIndexOf('/'));
    return basePath || '';
};

const BASE_PATH = getBasePath();

// Critical app shell resources (relative to base path)
const APP_SHELL = [
    `${BASE_PATH}/`,
    `${BASE_PATH}/index.php`,
    `${BASE_PATH}/assets/css/style.css`,
    `${BASE_PATH}/assets/js/app.js`,
    `${BASE_PATH}/assets/js/mobile-nav.js`,
    `${BASE_PATH}/assets/vendor/lightweight-charts.standalone.production.js`,
    `${BASE_PATH}/controllers/chartControllers.js`,
    `${BASE_PATH}/controllers/updateController.js`,
    `${BASE_PATH}/offline.html`
];

// Install event - cache app shell
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker, version:', VERSION);
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            console.log('[SW] Caching app shell');
            return cache.addAll(APP_SHELL).catch(err => {
                console.warn('[SW] Failed to cache some resources:', err);
                // Don't fail installation if some resources can't be cached
                return Promise.resolve();
            });
        }).then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker, version:', VERSION);
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== CACHE_NAME)
                    .map((name) => {
                        console.log('[SW] Deleting old cache:', name);
                        return caches.delete(name);
                    })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - network-first for API, cache-first for assets
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Only handle GET requests from same origin
    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Determine strategy based on request type
    const isAPI = url.pathname.includes('/helpers/') || 
                  url.pathname.includes('trades.php') ||
                  url.pathname.includes('update_pairs.php');
    
    const isAsset = url.pathname.includes('/assets/') ||
                    url.pathname.includes('.css') ||
                    url.pathname.includes('.js') ||
                    url.pathname.includes('.png') ||
                    url.pathname.includes('.jpg') ||
                    url.pathname.includes('.svg');

    if (isAPI) {
        // Network-first for API endpoints (fresh data preferred)
        event.respondWith(networkFirstStrategy(request));
    } else if (isAsset) {
        // Cache-first for static assets (fast loading)
        event.respondWith(cacheFirstStrategy(request));
    } else {
        // Stale-while-revalidate for pages
        event.respondWith(staleWhileRevalidateStrategy(request));
    }
});

// Network-first strategy (for APIs)
async function networkFirstStrategy(request) {
    try {
        const networkResponse = await fetch(request);
        // Cache successful responses
        if (networkResponse && networkResponse.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        console.log('[SW] Network failed, trying cache:', request.url);
        const cachedResponse = await caches.match(request);
        return cachedResponse || new Response(
            JSON.stringify({ error: 'Offline - cached data unavailable' }),
            { headers: { 'Content-Type': 'application/json' } }
        );
    }
}

// Cache-first strategy (for assets)
async function cacheFirstStrategy(request) {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
        return cachedResponse;
    }
    
    try {
        const networkResponse = await fetch(request);
        if (networkResponse && networkResponse.status === 200) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(request, networkResponse.clone());
        }
        return networkResponse;
    } catch (error) {
        console.error('[SW] Failed to fetch asset:', request.url);
        return new Response('Asset not available offline', { status: 404 });
    }
}

// Stale-while-revalidate strategy (for pages)
async function staleWhileRevalidateStrategy(request) {
    const cachedResponse = await caches.match(request);
    
    const fetchPromise = fetch(request).then(networkResponse => {
        if (networkResponse && networkResponse.status === 200) {
            const cache = caches.open(CACHE_NAME);
            cache.then(c => c.put(request, networkResponse.clone()));
        }
        return networkResponse;
    }).catch(() => {
        // If offline and no cache, show offline page
        return caches.match(`${BASE_PATH}/offline.html`);
    });
    
    return cachedResponse || fetchPromise;
}

// Listen for messages from clients
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
    
    if (event.data && event.data.type === 'GET_VERSION') {
        event.ports[0].postMessage({ version: VERSION });
    }
});

console.log('[SW] Service worker loaded, version:', VERSION, 'base path:', BASE_PATH);
