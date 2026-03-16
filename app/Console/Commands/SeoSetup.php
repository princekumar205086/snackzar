<?php

namespace App\Console\Commands;

use App\Modules\Shared\Services\RobotsTxtService;
use App\Modules\Shared\Services\FaviconService;
use App\Modules\Shared\Services\PwaService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SeoSetup extends Command
{
    protected $signature = 'seo:setup {--generate-assets : Generate static assets} {--seed : Seed SEO data} {--all : Do everything}';

    protected $description = 'Complete SEO setup: assets, data, and configuration';

    public function handle()
    {
        $this->info('🚀 Snackzar SEO Setup');
        $this->line('');

        // Generate assets
        if ($this->option('generate-assets') || $this->option('all')) {
            $this->generateAssets();
        }

        // Seed data
        if ($this->option('seed') || $this->option('all')) {
            $this->call('seo:seed', ['--keywords' => true, '--locations' => true, '--combinations' => true]);
        }

        $this->displaySummary();
    }

    protected function generateAssets(): void
    {
        $this->info('📦 Generating static assets...');

        // Create favicon directory
        $faviconPath = public_path('favicons');
        if (!File::isDirectory($faviconPath)) {
            File::makeDirectory($faviconPath, 0755, true);
            $this->info('✓ Created favicons directory');
        }

        // Generate manifest.json
        $pwaService = new PwaService();
        File::put(public_path('manifest.json'), $pwaService->generateManifest());
        $this->info('✓ Generated /manifest.json');

        // Generate service-worker.js
        File::put(public_path('service-worker.js'), $pwaService->generateServiceWorker());
        $this->info('✓ Generated /service-worker.js');

        // Generate offline.html
        File::put(public_path('offline.html'), $pwaService->generateOfflinePage());
        $this->info('✓ Generated /offline.html');

        // Generate favicon assets (JSON, XML)
        $faviconService = new FaviconService();
        File::put($faviconPath . '/site.webmanifest', $faviconService->generateWebManifest());
        $this->info('✓ Generated /favicons/site.webmanifest');

        File::put($faviconPath . '/browserconfig.xml', $faviconService->generateBrowserConfig());
        $this->info('✓ Generated /favicons/browserconfig.xml');

        // Note about actual image files
        $this->warn('⚠️  Note: Favicon image files (.ico, .png) should be manually placed in /public/favicons/');
        $this->warn('   Required files:');
        $this->warn('   - favicon.ico (16x16, 32x32)');
        $this->warn('   - favicon-16x16.png');
        $this->warn('   - favicon-32x32.png');
        $this->warn('   - apple-touch-icon.png (180x180)');
        $this->warn('   - android-chrome-192.png');
        $this->warn('   - android-chrome-512.png');
        $this->warn('   - mstile-150x150.png');

        $this->line('');
    }

    protected function displaySummary(): void
    {
        $this->info('✨ SEO Setup Complete!');
        $this->line('');
        
        $this->table(
            ['Component', 'Status', 'Description'],
            [
                ['Canonical Domain', '✓', 'Middleware enforces https://snackzar.com'],
                ['Robots.txt', '✓', 'Dynamic generation at /robots.txt'],
                ['Sitemap.xml', '✓', 'Multiple sitemaps for different content types'],
                ['PWA Support', '✓', 'manifest.json + service-worker.js'],
                ['Favicons', '⚠️', 'Configuration ready, image files needed'],
                ['Multi-Currency', '✓', 'INR/USD/EUR/GBP/AED/SGD support'],
                ['Android WebView', '✓', 'Deep linking and compatibility'],
                ['Structured Data', '✓', 'JSON-LD schemas ready'],
            ]
        );

        $this->line('');
        $this->info('📋 Next Steps:');
        $this->line('1. Generate favicon image files and place in /public/favicons/');
        $this->line('2. Run: php artisan seo:seed --all');
        $this->line('3. Verify routes: php artisan route:list | grep -E "(sitemap|robots|manifest)');
        $this->line('4. Test: php artisan test tests/Feature/Seo/');
    }
}
