/**
 * SNACKZAR SERVICE WORKER
 * Provides offline support, caching, and background sync for PWA
 * Last Updated: 2026-03-17
 */

const CACHE_VERSION = 'snackzar-v1';
const CACHE_URLS = [
    '/',
    '/offline',
    '/css/app.css',
    '/js/app.js',
    '/manifest.json',
    '/images/placeholder-product.svg',
    '/images/icons/icon-192x192.png',
];

const DYNAMIC_CACHE = 'snackzar-dynamic-v1';
const IMAGE_CACHE = 'snackzar-images-v1';

// Install event - cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_VERSION)
            .then((cache) => {
                return cache.addAll(CACHE_URLS);
            })
            .then(() => self.skipWaiting())
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== CACHE_VERSION && 
                        cacheName !== DYNAMIC_CACHE && 
                        cacheName !== IMAGE_CACHE) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event - network first, fallback to cache
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip chrome extensions
    if (url.protocol === 'chrome-extension:') {
        return;
    }

    // API requests - network first with cache fallback
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const cache = caches.open(DYNAMIC_CACHE);
                        cache.then((c) => c.put(request, response.clone()));
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request)
                        .then((response) => response || new Response(
                            JSON.stringify({ error: 'Offline' }),
                            { headers: { 'Content-Type': 'application/json' } }
                        ));
                })
        );
        return;
    }

    // Image requests - cache first with network fallback
    if (request.destination === 'image') {
        event.respondWith(
            caches.open(IMAGE_CACHE)
                .then((cache) => {
                    return cache.match(request)
                        .then((response) => {
                            return response || fetch(request)
                                .then((response) => {
                                    if (response.ok) {
                                        cache.put(request, response.clone());
                                    }
                                    return response;
                                })
                                .catch(() => caches.match('/images/placeholder-product.svg'));
                        });
                })
        );
        return;
    }

    // HTML pages - network first with cache fallback
    if (request.destination === 'document') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    if (response.ok) {
                        const cache = caches.open(DYNAMIC_CACHE);
                        cache.then((c) => c.put(request, response.clone()));
                    }
                    return response;
                })
                .catch(() => {
                    return caches.match(request)
                        .then((response) => {
                            return response || caches.match('/offline');
                        });
                })
        );
        return;
    }

    // CSS and JS - cache first with network fallback
    if (request.destination === 'style' || request.destination === 'script') {
        event.respondWith(
            caches.open(CACHE_VERSION)
                .then((cache) => {
                    return cache.match(request)
                        .then((response) => {
                            return response || fetch(request)
                                .then((response) => {
                                    if (response.ok) {
                                        cache.put(request, response.clone());
                                    }
                                    return response;
                                });
                        });
                })
        );
        return;
    }

    // Default - network first with cache fallback
    event.respondWith(
        fetch(request)
            .then((response) => {
                if (response.ok) {
                    const cache = caches.open(DYNAMIC_CACHE);
                    cache.then((c) => c.put(request, response.clone()));
                }
                return response;
            })
            .catch(() => {
                return caches.match(request);
            })
    );
});

// Background sync for orders and cart
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-orders') {
        event.waitUntil(syncOrders());
    }
    if (event.tag === 'sync-cart') {
        event.waitUntil(syncCart());
    }
});

// Push notifications
self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    const data = event.data.json();
    const options = {
        body: data.body,
        icon: data.icon || '/images/icons/icon-192x192.png',
        badge: data.badge || '/images/icons/badge-72x72.png',
        tag: data.tag || 'snackzar-notification',
        data: data.data || {},
        actions: [
            {
                action: 'open',
                title: 'Open'
            },
            {
                action: 'close',
                title: 'Close'
            }
        ]
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'Snackzar', options)
    );
});

// Handle notification clicks
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    if (event.action === 'close') {
        return;
    }

    const urlToOpen = event.notification.data.url || '/';

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true
        }).then((clientList) => {
            // Check if there's already a window open with the target URL
            for (let i = 0; i < clientList.length; i++) {
                const client = clientList[i];
                if (client.url === urlToOpen && 'focus' in client) {
                    return client.focus();
                }
            }
            // If not, open a new window
            if (clients.openWindow) {
                return clients.openWindow(urlToOpen);
            }
        })
    );
});

// Helper functions for background sync
async function syncOrders() {
    try {
        const orders = await getFromIndexedDB('pending-orders');
        for (const order of orders) {
            await fetch('/api/v1/orders', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(order)
            });
        }
        await deleteFromIndexedDB('pending-orders');
    } catch (error) {
        console.error('Order sync failed:', error);
    }
}

async function syncCart() {
    try {
        const cart = await getFromIndexedDB('pending-cart');
        if (cart) {
            await fetch('/api/v1/cart', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(cart)
            });
        }
        await deleteFromIndexedDB('pending-cart');
    } catch (error) {
        console.error('Cart sync failed:', error);
    }
}

// IndexedDB helper functions
function getFromIndexedDB(storeName) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('snackzar', 1);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction([storeName], 'readonly');
            const store = transaction.objectStore(storeName);
            const getAllRequest = store.getAll();
            getAllRequest.onsuccess = () => resolve(getAllRequest.result);
            getAllRequest.onerror = () => reject(getAllRequest.error);
        };
        request.onerror = () => reject(request.error);
    });
}

function deleteFromIndexedDB(storeName) {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('snackzar', 1);
        request.onsuccess = () => {
            const db = request.result;
            const transaction = db.transaction([storeName], 'readwrite');
            const store = transaction.objectStore(storeName);
            const clearRequest = store.clear();
            clearRequest.onsuccess = () => resolve();
            clearRequest.onerror = () => reject(clearRequest.error);
        };
        request.onerror = () => reject(request.error);
    });
}

// Message handling for client-initiated operations
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
