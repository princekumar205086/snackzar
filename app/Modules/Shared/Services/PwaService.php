<?php

namespace App\Modules\Shared\Services;

/**
 * PWA (Progressive Web App) Service
 * 
 * Generates PWA assets:
 * - manifest.json with app metadata
 * - service-worker.js for offline support
 * - Offline page
 * - Install prompts configuration
 */
class PwaService
{
    /**
     * Generate manifest.json content
     */
    public function generateManifest(): string
    {
        $domain = config('snackzar.seo.canonical_domain');
        
        $manifest = [
            'name' => 'Snackzar - Premium Healthy Snacks',
            'short_name' => 'Snackzar',
            'description' => 'Order fresh and organic snacks online with free delivery',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait-primary',
            'theme_color' => '#FF6B35',
            'background_color' => '#FFFFFF',
            'prefer_related_applications' => false,
            
            // App icons for different devices
            'icons' => [
                [
                    'src' => '/favicons/favicon-16x16.png',
                    'sizes' => '16x16',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/favicons/favicon-32x32.png',
                    'sizes' => '32x32',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/favicons/android-chrome-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/favicons/android-chrome-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/favicons/apple-touch-icon.png',
                    'sizes' => '180x180',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
            
            // Screenshots for app stores
            'screenshots' => [
                [
                    'src' => '/images/pwa-screenshot-540.png',
                    'sizes' => '540x720',
                    'type' => 'image/png',
                    'form_factor' => 'narrow',
                ],
                [
                    'src' => '/images/pwa-screenshot-1280.png',
                    'sizes' => '1280x720',
                    'type' => 'image/png',
                    'form_factor' => 'wide',
                ],
            ],
            
            // Shortcuts for quick actions
            'shortcuts' => [
                [
                    'name' => 'Browse Products',
                    'short_name' => 'Shop',
                    'description' => 'Browse our product collection',
                    'url' => '/products',
                    'icons' => [
                        [
                            'src' => '/favicons/favicon-192x192.png',
                            'sizes' => '192x192',
                            'type' => 'image/png',
                        ],
                    ],
                ],
                [
                    'name' => 'View Your Cart',
                    'short_name' => 'Cart',
                    'description' => 'Check items in your cart',
                    'url' => '/cart',
                    'icons' => [
                        [
                            'src' => '/favicons/favicon-192x192.png',
                            'sizes' => '192x192',
                            'type' => 'image/png',
                        ],
                    ],
                ],
                [
                    'name' => 'Read Our Blog',
                    'short_name' => 'Blog',
                    'description' => 'Read health tips and recipes',
                    'url' => '/blog',
                    'icons' => [
                        [
                            'src' => '/favicons/favicon-192x192.png',
                            'sizes' => '192x192',
                            'type' => 'image/png',
                        ],
                    ],
                ],
            ],
            
            // App categories
            'categories' => ['shopping', 'food & drink'],
            
            // Share target for share functionality
            'share_target' => [
                'action' => '/share',
                'method' => 'POST',
                'enctype' => 'multipart/form-data',
                'params' => [
                    'title' => 'title',
                    'text' => 'text',
                    'url' => 'url',
                    'files' => [
                        [
                            'name' => 'image',
                            'accept' => ['image/png', 'image/jpeg', 'image/gif'],
                        ],
                    ],
                ],
            ],
        ];

        return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate service worker script
     */
    public function generateServiceWorker(): string
    {
        return <<<'JAVASCRIPT'
const CACHE_VERSION = 1;
const CACHE_NAMES = {
    static: 'snackzar-static-v' + CACHE_VERSION,
    dynamic: 'snackzar-dynamic-v' + CACHE_VERSION,
    images: 'snackzar-images-v' + CACHE_VERSION,
};

// Files to cache on install
const STATIC_ASSETS = [
    '/',
    '/index.html',
    '/offline.html',
    '/css/app.css',
    '/js/app.js',
    '/favicons/favicon.ico',
    '/favicons/favicon-32x32.png',
    '/favicons/apple-touch-icon.png',
];

// Install: Cache static assets
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAMES.static).then((cache) => {
            return cache.addAll(STATIC_ASSETS);
        }).then(() => {
            self.skipWaiting();
        })
    );
});

// Activate: Clean up old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (!Object.values(CACHE_NAMES).includes(cacheName)) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => {
            self.clients.claim();
        })
    );
});

// Fetch: Serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests and external requests
    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Determine cache strategy
    if (request.destination === 'image') {
        // Images: cache first, fallback to network
        event.respondWith(
            caches.match(request).then((response) => {
                return response || fetch(request).then((response) => {
                    return caches.open(CACHE_NAMES.images).then((cache) => {
                        cache.put(request, response.clone());
                        return response;
                    });
                }).catch(() => {
                    return caches.match('/offline.html');
                });
            })
        );
    } else if (url.pathname.startsWith('/api/')) {
        // API: network first, fallback to cache
        event.respondWith(
            fetch(request)
                .then((response) => {
                    return caches.open(CACHE_NAMES.dynamic).then((cache) => {
                        cache.put(request, response.clone());
                        return response;
                    });
                })
                .catch(() => {
                    return caches.match(request) || new Response('API unavailable', {
                        status: 503,
                        statusText: 'Service Unavailable',
                    });
                })
        );
    } else {
        // Pages: cache first, fallback to network
        event.respondWith(
            caches.match(request).then((response) => {
                if (response) {
                    return response;
                }

                return fetch(request).then((response) => {
                    if (!response || response.status !== 200 || response.type === 'error') {
                        return response;
                    }

                    return caches.open(CACHE_NAMES.dynamic).then((cache) => {
                        cache.put(request, response.clone());
                        return response;
                    });
                }).catch(() => {
                    return caches.match('/offline.html');
                });
            })
        );
    }
});

// Background sync for orders
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-orders') {
        event.waitUntil(
            fetch('/api/sync-orders', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
        );
    }
});

// Push notifications
self.addEventListener('push', (event) => {
    const options = {
        body: event.data ? event.data.text() : 'New update from Snackzar',
        icon: '/favicons/favicon-192x192.png',
        badge: '/favicons/favicon-32x32.png',
        tag: 'snackzar-notification',
        requireInteraction: false,
    };

    event.waitUntil(
        self.registration.showNotification('Snackzar', options)
    );
});
JAVASCRIPT;
    }

    /**
     * Generate offline HTML page
     */
    public function generateOfflinePage(): string
    {
        return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Snackzar - Offline</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            text-align: center;
        }
        
        .icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        p {
            color: #666;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .status {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">📡</div>
        <h1>You're Offline</h1>
        <p>
            It looks like you've lost your internet connection. 
            You can still browse cached content or check back when you're online.
        </p>
        
        <button class="button" onclick="goBack()">Go Back</button>
        
        <div class="status">
            <p>Last updated: <span id="lastUpdate">-</span></p>
        </div>
    </div>
    
    <script>
        function goBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/';
            }
        }
        
        // Auto-refresh when back online
        window.addEventListener('online', () => {
            location.reload();
        });
        
        // Show last update time
        const lastUpdate = localStorage.getItem('snackzar-last-online');
        if (lastUpdate) {
            const time = new Date(lastUpdate);
            document.getElementById('lastUpdate').innerText = time.toLocaleString();
        }
    </script>
</body>
</html>
HTML;
    }

    /**
     * Get PWA head tags for layout
     */
    public function generateHeadTags(): string
    {
        $domain = config('snackzar.seo.canonical_domain');
        
        return <<<EOT
<!-- PWA Configuration -->
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#FF6B35">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Snackzar">
<meta name="application-name" content="Snackzar">
<meta name="msapplication-TileColor" content="#FF6B35">
<meta name="msapplication-config" content="/favicons/browserconfig.xml">

<!-- PWA Service Worker Registration -->
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/service-worker.js').then(registration => {
            console.log('PWA: Service Worker registered', registration);
        }).catch(error => {
            console.error('PWA: Service Worker registration failed', error);
        });
    }
    
    // Track online status
    window.addEventListener('online', () => {
        localStorage.setItem('snackzar-last-online', new Date().toISOString());
    });
    
    localStorage.setItem('snackzar-last-online', new Date().toISOString());
</script>
EOT;
    }

    /**
     * Check if PWA is enabled
     */
    public function isEnabled(): bool
    {
        return config('snackzar.seo.enable_pwa', true);
    }
}
