const STATIC_CACHE = 'suba-erp-static-v6';
const OFFLINE_URL = '/offline.html';
const STATIC_ASSETS = [
    OFFLINE_URL,
    '/css/styles.css',
    '/css/strategic-erp.css',
    '/js/app.js',
    '/js/strategic-erp.js',
    '/js/pwa.js',
    '/manifest.webmanifest',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/icons/icon-maskable-512.png',
    '/favicon.ico',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(STATIC_ASSETS))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('suba-erp-static-') && key !== STATIC_CACHE)
                    .map((key) => caches.delete(key)),
            ))
            .then(() => self.clients.claim()),
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request).catch(() => caches.match(OFFLINE_URL)),
        );
        return;
    }

    const protectedPath = url.pathname === '/'
        || url.pathname.startsWith('/api/')
        || url.pathname.startsWith('/erp-access')
        || url.pathname.startsWith('/certificate/')
        || url.pathname.startsWith('/verify/');

    if (protectedPath) return;

    const isStaticAsset = url.pathname.startsWith('/css/')
        || url.pathname.startsWith('/js/')
        || url.pathname.startsWith('/icons/')
        || url.pathname === '/manifest.webmanifest'
        || url.pathname === '/favicon.ico'
        || url.pathname === OFFLINE_URL;

    if (!isStaticAsset) return;

    event.respondWith(
        fetch(request).then((response) => {
            if (response.ok && response.type === 'basic') {
                const copy = response.clone();
                caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy));
            }

            return response;
        }).catch(() =>
            caches.match(request).then((cached) =>
                cached || caches.match(url.pathname, { ignoreSearch: true })
            )
        ),
    );
});

self.addEventListener('message', (event) => {
    if (event.data?.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
