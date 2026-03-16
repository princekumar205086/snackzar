<?php

namespace App\Console\Commands;

use Database\Seeders\BlogSeoContentSeeder;
use App\Modules\Shared\Services\SeoGeneratorService;
use Illuminate\Console\Command;

class SeedSeoData extends Command
{
    protected $signature = 'seo:seed {--all : Seed all SEO data} {--keywords : Seed keywords} {--locations : Seed locations} {--combinations : Generate city-keyword combinations} {--blog-posts : Seed 300+ SEO blog posts} {--limit=0 : Limit combinations generation}';

    protected $description = 'Seed SEO data: keywords, locations, and combinations';

    public function handle()
    {
        $seoGenerator = new SeoGeneratorService();

        if ($this->option('keywords') || $this->option('all')) {
            $this->info('Generating seed keywords...');
            $seoGenerator->generateSeedKeywords();
            $this->info('✓ Keywords seeded successfully');
        }

        if ($this->option('locations') || $this->option('all')) {
            $this->info('Seeding locations (cities and districts)...');
            $seoGenerator->seedLocations();
            $this->info('✓ Locations seeded successfully');
        }

        if ($this->option('combinations') || $this->option('all')) {
            $this->info('Generating city-keyword combinations...');
            $limit = (int)$this->option('limit');
            $generated = $seoGenerator->generateCityKeywordCombinations($limit);
            $this->info("✓ Generated {$generated} city-keyword combinations");
        }

        if ($this->option('blog-posts') || $this->option('all')) {
            $this->info('Generating and seeding 300+ SEO blog posts...');
            $this->call('db:seed', ['--class' => BlogSeoContentSeeder::class, '--force' => true]);
            $this->info('✓ SEO blog posts seeded successfully');
        }

        // Display statistics
        $stats = $seoGenerator->getStatistics();
        $this->info("\n📊 SEO Statistics:");
        $this->table(['Metric', 'Count'], [
            ['Total Cities', $stats['total_cities']],
            ['Active Cities', $stats['active_cities']],
            ['Total Keywords', $stats['total_keywords']],
            ['Active Keywords', $stats['active_keywords']],
            ['City-Keyword Combinations', $stats['city_keyword_combinations']],
            ['Active Landing Pages', $stats['active_landing_pages']],
            ['Target Indexable Pages', $stats['target_indexable_pages']],
        ]);
    }
}
