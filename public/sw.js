const CACHE_NAME = 'amm-pos-v1';

// Pre-cache the POS page shell on install
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.add('/pos').catch(() => {}))
    );
    self.skipWaiting();
});

// Clean up old caches on activate
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(keys.filter(k => k !== CACHE_NAME).map(k => caches.delete(k)))
        )
    );
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);

    // POS page: network-first so live data is always fresh; fall back to cache when offline
    if (url.pathname === '/pos') {
        event.respondWith(
            fetch(event.request)
                .then(res => {
                    const clone = res.clone();
                    caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    return res;
                })
                .catch(() => caches.match(event.request))
        );
        return;
    }

    // Static app assets: cache-first (fast, infrequently changed)
    if (url.pathname.startsWith('/app-assets/') || url.pathname.startsWith('/js/')) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                if (cached) return cached;
                return fetch(event.request).then(res => {
                    if (res && res.status === 200) {
                        const clone = res.clone();
                        caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    }
                    return res;
                });
            })
        );
        return;
    }

    // CDN resources (jQuery, Select2, fonts): cache-first
    if (url.hostname !== self.location.hostname) {
        event.respondWith(
            caches.match(event.request).then(cached => {
                if (cached) return cached;
                return fetch(event.request).then(res => {
                    if (res && res.status === 200 && res.type !== 'opaque') {
                        const clone = res.clone();
                        caches.open(CACHE_NAME).then(c => c.put(event.request, clone));
                    }
                    return res;
                }).catch(() => cached);
            })
        );
    }
});
