/**
 * ZYN Trade System - Service Worker
 * Enables offline functionality and caching
 *
 * CARA KERJA:
 * 1. Service worker akan cache file-file penting
 * 2. Ketika offline, user masih bisa akses halaman yang sudah di-cache
 * 3. Push notifications bisa dikirim meski browser tertutup
 */

const CACHE_NAME = 'zyn-trade-v3.0.0';
const OFFLINE_URL = '/offline.html';

// Files to cache immediately
const STATIC_CACHE = [
    '/',
    '/index.php',
    '/login.php',
    '/register.php',
    '/pricing.php',
    '/calculator.php',
    '/strategies.php',
    '/faq.php',
    '/mobile.php',
    '/offline.html',
    '/assets/css/style.css',
    '/assets/css/dashboard.css',
    '/assets/js/main.js',
    '/manifest.json',
    'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Orbitron:wght@400;500;600;700;800;900&display=swap'
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing Service Worker...');

    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                console.log('[SW] Caching static assets');
                return cache.addAll(STATIC_CACHE);
            })
            .then(() => {
                console.log('[SW] Static assets cached');
                return self.skipWaiting();
            })
            .catch((error) => {
                console.error('[SW] Cache failed:', error);
            })
    );
});

// Activate event - clean old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating Service Worker...');

    event.waitUntil(
        caches.keys()
            .then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            console.log('[SW] Deleting old cache:', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('[SW] Service Worker activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    // Skip non-GET requests
    if (event.request.method !== 'GET') {
        return;
    }

    // Skip API calls - always fetch from network
    if (event.request.url.includes('/api/')) {
        return;
    }

    event.respondWith(
        caches.match(event.request)
            .then((cachedResponse) => {
                if (cachedResponse) {
                    // Return cached version
                    return cachedResponse;
                }

                // Fetch from network
                return fetch(event.request)
                    .then((response) => {
                        // Don't cache if not a valid response
                        if (!response || response.status !== 200 || response.type !== 'basic') {
                            return response;
                        }

                        // Clone the response
                        const responseToCache = response.clone();

                        // Cache the fetched resource
                        caches.open(CACHE_NAME)
                            .then((cache) => {
                                cache.put(event.request, responseToCache);
                            });

                        return response;
                    })
                    .catch(() => {
                        // If both cache and network fail, show offline page
                        if (event.request.mode === 'navigate') {
                            return caches.match(OFFLINE_URL);
                        }
                    });
            })
    );
});

// Push notification event
self.addEventListener('push', (event) => {
    console.log('[SW] Push notification received');

    let data = {
        title: 'ZYN Trade Signal',
        body: 'Ada signal trading baru!',
        icon: '/assets/icons/icon-192x192.png',
        badge: '/assets/icons/badge-72x72.png',
        tag: 'zyn-signal',
        data: {
            url: '/dashboard.php'
        }
    };

    if (event.data) {
        try {
            data = { ...data, ...event.data.json() };
        } catch (e) {
            data.body = event.data.text();
        }
    }

    const options = {
        body: data.body,
        icon: data.icon,
        badge: data.badge,
        tag: data.tag,
        vibrate: [200, 100, 200],
        requireInteraction: true,
        actions: [
            { action: 'open', title: 'Buka Dashboard' },
            { action: 'close', title: 'Tutup' }
        ],
        data: data.data
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Notification click event
self.addEventListener('notificationclick', (event) => {
    console.log('[SW] Notification clicked');

    event.notification.close();

    if (event.action === 'close') {
        return;
    }

    const urlToOpen = event.notification.data?.url || '/dashboard.php';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                // Check if there's already a window open
                for (const client of windowClients) {
                    if (client.url.includes(urlToOpen) && 'focus' in client) {
                        return client.focus();
                    }
                }
                // Open new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

// Background sync event (for offline actions)
self.addEventListener('sync', (event) => {
    console.log('[SW] Background sync:', event.tag);

    if (event.tag === 'sync-trades') {
        event.waitUntil(syncTrades());
    }
});

// Sync pending trades when back online
async function syncTrades() {
    try {
        const cache = await caches.open('zyn-pending-actions');
        const requests = await cache.keys();

        for (const request of requests) {
            const response = await cache.match(request);
            const data = await response.json();

            // Send to server
            await fetch('/api/sync-trade.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            // Remove from cache
            await cache.delete(request);
        }

        console.log('[SW] Trades synced successfully');
    } catch (error) {
        console.error('[SW] Sync failed:', error);
    }
}

// Periodic background sync (for checking signals)
self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'check-signals') {
        event.waitUntil(checkNewSignals());
    }
});

async function checkNewSignals() {
    try {
        const response = await fetch('/api/check-signals.php');
        const data = await response.json();

        if (data.hasNewSignal) {
            self.registration.showNotification('Signal Baru!', {
                body: data.message,
                icon: '/assets/icons/icon-192x192.png',
                tag: 'new-signal'
            });
        }
    } catch (error) {
        console.error('[SW] Check signals failed:', error);
    }
}
