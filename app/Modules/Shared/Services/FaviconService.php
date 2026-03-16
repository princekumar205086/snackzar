<?php

namespace App\Modules\Shared\Services;

use Illuminate\Support\Facades\File;

/**
 * Favicon Service
 * 
 * Generates and manages favicon assets:
 * - favicon.ico
 * - favicon-16x16.png
 * - favicon-32x32.png
 * - apple-touch-icon.png
 * - android-chrome-icon.png
 * - webmanifest
 */
class FaviconService
{
    protected string $faviconPath;
    protected array $config = [
        'brand_color' => '#FF6B35', // Orange for Snackzar
        'background_color' => '#FFFFFF',
        'theme_color' => '#FF6B35',
    ];

    public function __construct()
    {
        $this->faviconPath = public_path('favicons');
    }

    /**
     * Generate favicon HTML tags for head
     */
    public function generateHeadTags(): string
    {
        $domain = config('snackzar.seo.canonical_domain');

        return <<<EOT
<!-- Favicon Links -->
<link rel="icon" type="image/x-icon" href="/favicon.ico">
<link rel="apple-touch-icon" href="/favicons/apple-touch-icon.png" sizes="180x180">
<link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
<link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
<link rel="manifest" href="/favicons/site.webmanifest">
<link rel="mask-icon" href="/favicons/safari-pinned-tab.svg" color="{$this->config['brand_color']}">
<meta name="msapplication-TileColor" content="{$this->config['background_color']}">
<meta name="msapplication-config" content="/favicons/browserconfig.xml">
<meta name="theme-color" content="{$this->config['theme_color']}">
EOT;
    }

    /**
     * Generate site.webmanifest file content
     */
    public function generateWebManifest(): string
    {
        $manifest = [
            'name' => 'Snackzar - Premium Healthy Snacks',
            'short_name' => 'Snackzar',
            'description' => 'Fresh and organic snacks delivered to your doorstep',
            'start_url' => '/',
            'scope' => '/',
            'display' => 'standalone',
            'theme_color' => $this->config['theme_color'],
            'background_color' => $this->config['background_color'],
            'orientation' => 'portrait-primary',
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
            'categories' => ['shopping', 'food'],
            'screenshots' => [
                [
                    'src' => '/favicons/screenshot-540.png',
                    'sizes' => '540x720',
                    'type' => 'image/png',
                    'form_factor' => 'narrow',
                ],
                [
                    'src' => '/favicons/screenshot-1280.png',
                    'sizes' => '1280x720',
                    'type' => 'image/png',
                    'form_factor' => 'wide',
                ],
            ],
        ];

        return json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Generate browserconfig.xml for Windows
     */
    public function generateBrowserConfig(): string
    {
        return <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<browserconfig>
  <msapplication>
    <tile>
      <square150x150logo src="/favicons/mstile-150x150.png"/>
      <TileColor>{$this->config['background_color']}</TileColor>
    </tile>
  </msapplication>
</browserconfig>
EOT;
    }

    /**
     * Check if favicon assets exist
     */
    public function assetsExist(): bool
    {
        $requiredFiles = [
            'favicon.ico',
            'apple-touch-icon.png',
            'favicon-32x32.png',
            'favicon-16x16.png',
            'site.webmanifest',
            'browserconfig.xml',
        ];

        foreach ($requiredFiles as $file) {
            if ($file === 'favicon.ico') {
                if (!File::exists(public_path($file))) {
                    return false;
                }
            } else {
                if (!File::exists($this->faviconPath . '/' . $file)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get config for favicon generation
     */
    public function getConfig(): array
    {
        return array_merge($this->config, [
            'brand' => 'Snackzar',
            'logo_path' => public_path('logo.png'),
            'favicon_path' => $this->faviconPath,
        ]);
    }
}
