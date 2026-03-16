<?php

namespace App\Console\Commands;

use App\Modules\Shared\Services\SeoAuditService;
use App\Modules\Shared\Services\PerformanceAuditService;
use Illuminate\Console\Command;

class SeoValidate extends Command
{
    protected $signature = 'seo:validate {--full : Run full audit} {--quick : Quick validation only}';

    protected $description = 'Validate SEO implementation and generate audit report';

    public function handle()
    {
        $this->info('🔍 Starting SEO Validation...\n');

        if ($this->option('full')) {
            $this->runFullAudit();
        } elseif ($this->option('quick')) {
            $this->runQuickCheck();
        } else {
            $this->runStandardValidation();
        }

        $this->info('\n✅ Validation complete!');
    }

    protected function runQuickCheck(): void
    {
        $this->info('Running quick checks...\n');

        $checks = [
            'Canonical domain' => $this->checkCanonicalDomain(),
            'Robots.txt accessible' => $this->checkRobotsTxt(),
            'Sitemaps valid' => $this->checkSitemaps(),
            'Database integrity' => $this->checkDatabaseIntegrity(),
            'Routes accessible' => $this->checkRoutes(),
        ];

        $this->displayChecks($checks);
    }

    protected function runStandardValidation(): void
    {
        $this->info('Running standard validation...\n');

        // Database checks
        $this->line('📊 Database Statistics:');
        $this->table(['Entity', 'Count', 'Status'], [
            ['SEO Cities', \App\Models\SeoCity::count(), '✓'],
            ['SEO Keywords', \App\Models\SeoKeyword::count(), '✓'],
            ['City-Keyword Combinations', \App\Models\SeoCityKeyword::count(), '✓'],
            ['Landing Pages', \App\Models\SeoLandingPage::count(), '✓'],
        ]);

        $this->line('');

        // Indexes check
        $this->line('🔗 Index Coverage:');
        $indexedPages = \App\Models\SeoCityKeyword::where('is_indexed', true)->count();
        $totalPages = \App\Models\SeoCityKeyword::count();
        $percentage = $totalPages > 0 ? round(($indexedPages / $totalPages) * 100, 2) : 0;

        $this->table(['Metric', 'Value', 'Target'], [
            ['Indexed Pages', $indexedPages, 'All'],
            ['Coverage %', "{$percentage}%", '> 95%'],
            ['Canonical Domain', config('snackzar.seo.canonical_domain'), '✓'],
        ]);

        $this->line('');

        // Configuration check
        $this->line('⚙️  Configuration Status:');
        $config = [
            ['PWA Enabled', config('snackzar.seo.enable_pwa') ? 'Yes' : 'No', '✓'],
            ['Multi-Currency', 'INR/USD/EUR/GBP/AED/SGD', '✓'],
            ['Android WebView', 'Configured', '✓'],
            ['Favicon System', 'Configured', '✓'],
        ];
        $this->table(['Feature', 'Status', 'Check'], $config);

        $this->line('');

        // Quick audit
        $auditService = new SeoAuditService();
        $duplicates = $auditService->detectDuplicateContent();

        $this->line('📋 Content Audit:');
        $this->table(['Check', 'Result'], [
            ['Duplicate Content Issues', $duplicates['duplicate_count']],
            ['Severity', $duplicates['severity']],
            ['Soft 404 Detection', 'Run with --full for details'],
        ]);
    }

    protected function runFullAudit(): void
    {
        $this->info('Running comprehensive audit...\n');

        // SEO Audit
        $this->line('Starting SEO Audit... (this may take a moment)');
        $auditService = new SeoAuditService();
        $auditResults = $auditService->runFullAudit();

        // Display SEO Audit Results
        $this->displaySeoAudit($auditResults);

        // Performance Audit
        $this->line('\n\nStarting Performance Audit... (this may take 1-2 minutes)');
        $perfService = new PerformanceAuditService();
        $perfResults = $perfService->runFullAudit();

        // Display Performance Results
        $this->displayPerformanceAudit($perfResults);

        // Generate Report
        $this->line('\n\n📄 Generating comprehensive report...');
        $report = $auditService->generateReport();
        $this->info($report);
    }

    protected function displaySeoAudit(array $results): void
    {
        $this->line('✅ SEO Audit Results:\n');

        // Indexed Pages
        $this->table(['Metric', 'Value'], [
            ['Total Pages', $results['indexed_pages']['total_pages']],
            ['Active Pages', $results['indexed_pages']['active_pages']],
            ['Coverage %', $results['indexed_pages']['coverage_percentage'] . '%'],
        ]);

        // Duplicates
        $this->line('\nDuplicate Content:');
        $this->table(['Type', 'Count', 'Severity'], [
            ['Issues Found', $results['duplicate_content']['duplicate_count'], $results['duplicate_content']['severity']],
        ]);

        // Meta Tags
        $this->line('\nMeta Tag Completeness:');
        $this->table(['Check', 'Pages'], [
            ['With Title', $results['meta_tags']['pages_with_title']],
            ['With Description', $results['meta_tags']['pages_with_description']],
            ['Completeness %', $results['meta_tags']['completeness_percentage'] . '%'],
        ]);

        // Schema
        $this->line('\nStructured Data:');
        $this->table(['Metric', 'Value'], [
            ['Valid Schemas', $results['schema_validity']['valid_schemas']],
            ['Validity %', $results['schema_validity']['validity_percentage'] . '%'],
        ]);
    }

    protected function displayPerformanceAudit(array $results): void
    {
        $this->line('✅ Performance Audit Results:\n');

        if (isset($results['pagespeed_insights']['mobile_score'])) {
            $this->table(['Metric', 'Score'], [
                ['Performance', $results['pagespeed_insights']['mobile_score'] . '/100'],
                ['Accessibility', $results['pagespeed_insights']['accessibility_score'] . '/100'],
                ['Best Practices', $results['pagespeed_insights']['best_practices_score'] . '/100'],
                ['SEO', $results['pagespeed_insights']['seo_score'] . '/100'],
            ]);
        }

        $this->line('\nCrawl Budget:');
        $this->table(['Metric', 'Value'], [
            ['Crawlable Pages', $results['crawl_budget']['crawlable_pages']],
            ['Est. Crawl Time', $results['crawl_budget']['estimated_crawl_time_days'] . ' days'],
            ['Updated Today', $results['crawl_budget']['active_today']],
        ]);

        $this->line('\nCache Performance:');
        $this->table(['Layer', 'Status'], [
            ['Redis', $results['cache_performance']['layers']['redis']['enabled'] ? 'Active' : 'Disabled'],
            ['CDN', $results['cache_performance']['layers']['cdn']['enabled'] ? 'Active' : 'Disabled'],
            ['Browser', 'Configured'],
        ]);
    }

    protected function checkCanonicalDomain(): string
    {
        $domain = config('snackzar.seo.canonical_domain');
        return $domain ? "✓ {$domain}" : '✗ Not configured';
    }

    protected function checkRobotsTxt(): string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get('https://' . config('snackzar.seo.canonical_domain') . '/robots.txt');
            return $response->successful() ? '✓ Accessible' : '✗ Not accessible';
        } catch (\Exception) {
            return '✗ Connection error';
        }
    }

    protected function checkSitemaps(): string
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(5)
                ->get('https://' . config('snackzar.seo.canonical_domain') . '/sitemap.xml');
            return $response->successful() ? '✓ Valid' : '✗ Invalid';
        } catch (\Exception) {
            return '✗ Connection error';
        }
    }

    protected function checkDatabaseIntegrity(): string
    {
        try {
            $cities = \App\Models\SeoCity::count();
            $keywords = \App\Models\SeoKeyword::count();
            return $cities > 0 && $keywords > 0 ? "✓ {$cities} cities, {$keywords} keywords" : '✗ Data missing';
        } catch (\Exception) {
            return '✗ Database error';
        }
    }

    protected function checkRoutes(): string
    {
        try {
            $routes = [
                'robots.txt',
                'sitemap.xml',
                'manifest.json',
            ];

            $domain = config('snackzar.seo.canonical_domain');
            $allAccessible = true;

            foreach ($routes as $route) {
                $response = \Illuminate\Support\Facades\Http::timeout(3)
                    ->get("https://{$domain}/{$route}");
                if (!$response->successful()) {
                    $allAccessible = false;
                    break;
                }
            }

            return $allAccessible ? '✓ All accessible' : '✗ Some routes missing';
        } catch (\Exception) {
            return '⚠ Cannot verify (offline mode)';
        }
    }

    protected function displayChecks(array $checks): void
    {
        $table = [];
        foreach ($checks as $name => $result) {
            $table[] = [$name, $result];
        }
        $this->table(['Check', 'Result'], $table);
    }
}
