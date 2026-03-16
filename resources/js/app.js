import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || 'Snackzar';
const fallbackImage = '/images/placeholder-product.svg';

// ===== PWA & SERVICE WORKER REGISTRATION =====
if ('serviceWorker' in navigator && 'caches' in window) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/service-worker.js', {
                scope: '/',
            });
            console.log('✓ Service Worker registered');
            
            // Check for updates every 60 seconds
            setInterval(() => registration.update(), 60000);
            
            // Handle controller changes
            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (!refreshing) {
                    refreshing = true;
                    window.location.reload();
                }
            });
        } catch (error) {
            console.warn('Service Worker registration failed:', error);
        }
    });
}

// ===== PWA INSTALL PROMPT =====
let deferredPrompt;
if ('BeforeInstallPromptEvent' in window) {
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        window.pwaPrompt = { show: () => deferredPrompt?.prompt() };
    });
}

// ===== IMAGE FALLBACK =====
function applyImageFallback(img) {
    if (!img || img.dataset.fallbackApplied === '1') return;
    img.dataset.fallbackApplied = '1';
    img.src = fallbackImage;
    if (!img.alt) {
        img.alt = 'Image unavailable';
    }
}

if (typeof window !== 'undefined') {
    document.addEventListener('error', (event) => {
        if (event.target instanceof HTMLImageElement) {
            applyImageFallback(event.target);
        }
    }, true);
    
    // Offline/Online detection
    window.addEventListener('offline', () => {
        console.log('⚠ Offline mode');
    });
    window.addEventListener('online', () => {
        console.log('✓ Back online');
    });
}

// ===== INERTIA APP INITIALIZATION =====

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#f59e0b',
    },
});