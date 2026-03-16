<?php

namespace App\Modules\Shared\Services;

use Illuminate\Http\Request;

/**
 * Android WebView Compatibility Service
 * 
 * Ensures proper functionality inside Android WebView:
 * - Responsive UI support
 * - Deep linking handling
 * - Cookie and session management
 * - Secure communication
 */
class AndroidWebViewCompatibilityService
{
    /**
     * Check if request is from Android WebView
     */
    public static function isAndroidWebView(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');
        
        return (
            strpos($userAgent, 'Android') !== false &&
            strpos($userAgent, 'WebView') !== false ||
            strpos($userAgent, 'wv;') !== false
        );
    }

    /**
     * Get Android WebView compatible headers
     */
    public static function getCompatibleHeaders(): array
    {
        return [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'ALLOW-FROM',
            'X-UA-Compatible' => 'IE=edge',
        ];
    }

    /**
     * Generate meta tags for WebView compatibility
     */
    public static function generateMetaTags(): string
    {
        return <<<EOT
<!-- Android WebView Compatibility -->
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes, viewport-fit=cover">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="format-detection" content="telephone=no">
<meta name="format-detection" content="address=no">

<!-- Android App Links for Deep Linking -->
<link rel="alternate" href="android-app://com.snackzar/https/snackzar.com/">

<!-- Ensure proper rendering -->
<style>
    /* Prevent layout shift in WebView -->
    html, body {
        width: 100%;
        height: 100%;
        margin: 0;
        padding: 0;
        -webkit-user-select: none;
        -webkit-touch-callout: none;
    }
    
    /* Fix input focus on WebView */
    input, textarea, select {
        -webkit-user-select: text;
    }
    
    /* Prevent zoom on double tap */
    input[type="text"],
    input[type="email"],
    input[type="password"],
    textarea {
        font-size: 16px !important;
    }
    
    /* Fix scrolling issues */
    -webkit-overflow-scrolling: touch;
    
    /* Prevent image dragging */
    img {
        -webkit-user-drag: none;
        -webkit-user-select: none;
    }
</style>

<!-- JavaScript Bridge for Native Communication -->
<script>
    // Expose JavaScript interface for WebView
    window.AndroidInterface = {
        // Share to native
        share: function(title, text, url) {
            if (window.Android && window.Android.share) {
                window.Android.share(title, text, url);
            }
        },
        
        // Handle deep links
        openDeepLink: function(url) {
            if (window.Android && window.Android.openDeepLink) {
                window.Android.openDeepLink(url);
            } else {
                window.location.href = url;
            }
        },
        
        // Native toast notification
        showToast: function(message) {
            if (window.Android && window.Android.showToast) {
                window.Android.showToast(message);
            }
        },
        
        // Get device info
        getDeviceInfo: function() {
            if (window.Android && window.Android.getDeviceInfo) {
                return window.Android.getDeviceInfo();
            }
            return null;
        }
    };
    
    // Listen for WebView ready
    if (window.Android) {
        console.log('Android WebView detected');
        document.documentElement.setAttribute('data-webview', 'android');
    }
</script>
EOT;
    }

    /**
     * Handle deep linking
     */
    public static function handleDeepLink(Request $request): ?string
    {
        // Pattern: snackzar://product/slug
        // Pattern: snackzar://category/slug
        // Pattern: snackzar://order/id
        $deepLink = $request->query('mobile_link') ?? $request->query('android_link');

        if (!$deepLink) {
            return null;
        }

        // Parse and validate deep link
        return self::parseDeepLink($deepLink);
    }

    /**
     * Parse and validate deep link format
     */
    protected static function parseDeepLink(string $link): ?string
    {
        $pattern = '/^snackzar:\/\/([a-z]+)\/(.+)$/i';
        
        if (!preg_match($pattern, $link, $matches)) {
            return null;
        }

        [$_, $type, $slug] = $matches;

        return match (strtolower($type)) {
            'product' => route('products.show', $slug),
            'category' => route('category.show', $slug),
            'blog' => route('blog.show', $slug),
            'order' => route('orders.show', $slug),
            default => null,
        };
    }

    /**
     * Get JWT token for session management in WebView
     */
    public static function getWebViewSessionToken(): string
    {
        // Generate secure token for WebView communication
        return hash('sha256', implode('|', [
            config('app.key'),
            now()->timestamp,
            auth()->id() ?? 'guest',
        ]));
    }

    /**
     * Verify WebView request authenticity
     */
    public static function verifyWebViewRequest(Request $request): bool
    {
        $token = $request->header('X-WebView-Token');
        $timestamp = $request->header('X-WebView-Timestamp');

        if (!$token || !$timestamp) {
            return false;
        }

        // Verify timestamp is recent (within 5 minutes)
        if (abs(time() - intval($timestamp)) > 300) {
            return false;
        }

        // Reconstruct and compare token
        $expectedToken = hash('sha256', implode('|', [
            config('app.key'),
            $timestamp,
            auth()->id() ?? 'guest',
        ]));

        return hash_equals($token, $expectedToken);
    }

    /**
     * Get Android WebView specific configuration
     */
    public static function getConfiguration(): array
    {
        return [
            'supports_push' => true,
            'supports_biometric' => true,
            'supports_native_share' => true,
            'supports_deep_linking' => true,
            'session_timeout' => 3600,
            'auto_login' => true,
            'offline_sync' => true,
        ];
    }
}
