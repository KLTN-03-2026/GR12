const CACHE_NAME = 'foodxpress-cache-v1';
const urlsToCache = [
    '/',
    '/manifest.json',
    '/icon.svg',
    '/offline.html' // A fallback page if we had one
];

// Install event: cache core assets
self.addEventListener('install', event => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache).catch(err => console.log('Some assets failed to cache', err));
            })
    );
});

// Activate event: clean up old caches
self.addEventListener('activate', event => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
});

// Fetch event: Network first, fallback to cache
self.addEventListener('fetch', event => {
    // Only intercept GET requests
    if (event.request.method !== 'GET') return;
    
    // Ignore API calls and broadcasting requests
    if (event.request.url.includes('/api/') || event.request.url.includes('/broadcasting/')) return;

    event.respondWith(
        fetch(event.request)
            .then(response => {
                // If valid response, clone and cache it for future use
                if (!response || response.status !== 200 || response.type !== 'basic') {
                    return response;
                }
                const responseToCache = response.clone();
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(event.request, responseToCache);
                });
                return response;
            })
            .catch(() => {
                // Network failed, try cache
                return caches.match(event.request).then(response => {
                    if (response) {
                        return response;
                    }
                    // If no cache, we could return a fallback offline page here
                    return caches.match('/');
                });
            })
    );
});
