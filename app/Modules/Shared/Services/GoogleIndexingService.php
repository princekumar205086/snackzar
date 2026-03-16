<?php

namespace App\Modules\Shared\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Indexing API Service
 * 
 * Automatically notifies Google when new pages are created or updated:
 * - Immediate indexing requests via IndexNow API
 * - URL submission to Google Search Console
 * - Sitemap ping
 */
class GoogleIndexingService
{
    protected string $googleServiceAccountJson;
    protected string $searchConsoleApiUrl = 'https://indexing.googleapis.com/batch';
    protected string $indexNowApiUrl = 'https://www.bing.com/indexnow';
    protected array $indexNowKeys = [];

    public function __construct()
    {
        $this->googleServiceAccountJson = config('services.google.service_account_json_path');
        $this->indexNowKeys = config('services.indexnow.api_keys', []);
    }

    /**
     * Submit URL to Google Indexing API (Premium service - requires quota)
     * Alternative: Use IndexNow which is free
     */
    public function submitUrlToGoogle(string $url): array
    {
        try {
            // Note: Requires Google Service Account with Google Search Console API enabled
            // This is mainly for web pages, not for API endpoints
            
            $accessToken = $this->getGoogleAccessToken();
            
            if (!$accessToken) {
                Log::warning('Google Indexing: No access token available');
                return [
                    'success' => false,
                    'message' => 'Google Service Account not configured',
                ];
            }

            // Submit via Google Search Console Indexing API
            $response = Http::withToken($accessToken)
                ->post($this->searchConsoleApiUrl, [
                    'requests' => [
                        [
                            'indexingType' => 'URL_UPDATED',
                            'method' => 'GET',
                            'url' => $url,
                        ],
                    ],
                ]);

            return [
                'success' => $response->successful(),
                'status_code' => $response->status(),
                'url' => $url,
                'service' => 'Google Indexing API',
                'message' => $response->successful() ? 'URL submitted successfully' : 'Failed to submit URL',
            ];
        } catch (\Exception $e) {
            Log::error('Google Indexing API error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => true,
            ];
        }
    }

    /**
     * Submit URL to IndexNow (Bing/Microsoft)
     * Free alternative to Google Indexing API
     * Bing will forward to Google
     */
    public function submitUrlToIndexNow(string $url): array
    {
        try {
            if (empty($this->indexNowKeys)) {
                Log::warning('IndexNow: API key not configured');
                return [
                    'success' => false,
                    'message' => 'IndexNow API key not configured',
                ];
            }

            $apiKey = is_array($this->indexNowKeys) ? $this->indexNowKeys[0] : $this->indexNowKeys;

            $response = Http::post($this->indexNowApiUrl, [
                'host' => config('snackzar.seo.canonical_domain'),
                'key' => $apiKey,
                'keyLocation' => 'https://' . config('snackzar.seo.canonical_domain') . '/indexnow.txt',
                'urlList' => [$url],
            ]);

            return [
                'success' => $response->status() === 200,
                'status_code' => $response->status(),
                'url' => $url,
                'service' => 'IndexNow (Bing)',
                'message' => $response->successful() ? 'URL submitted via IndexNow' : 'IndexNow submission failed',
            ];
        } catch (\Exception $e) {
            Log::error('IndexNow API error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => true,
            ];
        }
    }

    /**
     * Bulk submit URLs to IndexNow
     */
    public function bulkSubmitToIndexNow(array $urls, int $maxPerRequest = 10000): array
    {
        try {
            if (empty($this->indexNowKeys)) {
                return [
                    'success' => false,
                    'message' => 'IndexNow API key not configured',
                ];
            }

            $apiKey = is_array($this->indexNowKeys) ? $this->indexNowKeys[0] : $this->indexNowKeys;
            $results = [];

            // Split URLs into chunks (IndexNow max 10,000 per request)
            $chunks = array_chunk($urls, $maxPerRequest);

            foreach ($chunks as $chunk) {
                $response = Http::post($this->indexNowApiUrl, [
                    'host' => config('snackzar.seo.canonical_domain'),
                    'key' => $apiKey,
                    'keyLocation' => 'https://' . config('snackzar.seo.canonical_domain') . '/indexnow.txt',
                    'urlList' => $chunk,
                ]);

                $results[] = [
                    'chunk_size' => count($chunk),
                    'status' => $response->status(),
                    'success' => $response->status() === 200,
                ];
            }

            return [
                'success' => count(array_filter($results, fn($r) => $r['success'])) === count($results),
                'total_urls' => count($urls),
                'chunks' => count($results),
                'results' => $results,
                'message' => 'Bulk submission to IndexNow completed',
            ];
        } catch (\Exception $e) {
            Log::error('IndexNow bulk submission error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => true,
            ];
        }
    }

    /**
     * Ping sitemaps to Google and Bing
     */
    public function pingSitemaps(): array
    {
        $domain = config('snackzar.seo.canonical_domain');
        $sitemaps = [
            "https://{$domain}/sitemap-index.xml",
            "https://{$domain}/sitemap-main.xml",
            "https://{$domain}/sitemap-products.xml",
            "https://{$domain}/sitemap-blog.xml",
            "https://{$domain}/sitemap-cities.xml",
        ];

        $results = [
            'google' => [],
            'bing' => [],
        ];

        // Ping Google
        foreach ($sitemaps as $sitemap) {
            try {
                $response = Http::timeout(5)->get("https://www.google.com/ping?sitemap={$sitemap}");
                $results['google'][] = [
                    'sitemap' => $sitemap,
                    'status' => $response->status(),
                    'success' => $response->status() === 200,
                ];
            } catch (\Exception $e) {
                $results['google'][] = [
                    'sitemap' => $sitemap,
                    'error' => $e->getMessage(),
                    'success' => false,
                ];
            }
        }

        // Ping Bing
        foreach ($sitemaps as $sitemap) {
            try {
                $response = Http::timeout(5)->get("https://www.bing.com/ping?sitemap={$sitemap}");
                $results['bing'][] = [
                    'sitemap' => $sitemap,
                    'status' => $response->status(),
                    'success' => $response->status() === 200,
                ];
            } catch (\Exception $e) {
                $results['bing'][] = [
                    'sitemap' => $sitemap,
                    'error' => $e->getMessage(),
                    'success' => false,
                ];
            }
        }

        return [
            'service' => 'Sitemap Pinging',
            'timestamp' => now()->toIso8601String(),
            'results' => $results,
        ];
    }

    /**
     * Get or generate IndexNow key file
     */
    public function getIndexNowKeyFile(): string
    {
        $apiKey = is_array($this->indexNowKeys) ? $this->indexNowKeys[0] : $this->indexNowKeys;
        return $apiKey;
    }

    /**
     * Validate URL before submission
     */
    public function validateUrl(string $url): bool
    {
        // Check canonical domain
        $domain = config('snackzar.seo.canonical_domain');
        
        if (!str_contains($url, $domain)) {
            return false;
        }

        // Check URL is accessible
        try {
            $response = Http::timeout(5)->head($url);
            return $response->status() === 200;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Get Google access token for Indexing API
     */
    protected function getGoogleAccessToken(): ?string
    {
        try {
            if (!$this->googleServiceAccountJson || !file_exists($this->googleServiceAccountJson)) {
                return null;
            }

            $serviceAccount = json_decode(file_get_contents($this->googleServiceAccountJson), true);

            // Get access token using service account
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_email' => $serviceAccount['client_email'],
                'private_key' => $serviceAccount['private_key'],
                'scopes' => 'https://www.googleapis.com/auth/indexing',
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $this->createJwt($serviceAccount),
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google token generation error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create JWT for service account
     */
    protected function createJwt(array $serviceAccount): string
    {
        $header = base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
        ]));

        $payload = base64_encode(json_encode([
            'iss' => $serviceAccount['client_email'],
            'scope' => 'https://www.googleapis.com/auth/indexing',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => time() + 3600,
            'iat' => time(),
        ]));

        $signature = '';
        openssl_sign("$header.$payload", $signature, $serviceAccount['private_key'], 'sha256');
        $signature = base64_encode($signature);

        return "$header.$payload.$signature";
    }

    /**
     * Log submission for tracking
     */
    public function logSubmission(string $url, string $service, bool $success, ?string $message = null): void
    {
        Log::info("Indexing submission: {$service}", [
            'url' => $url,
            'success' => $success,
            'message' => $message,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
