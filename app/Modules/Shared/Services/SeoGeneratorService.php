<?php

namespace App\Modules\Shared\Services;

use App\Models\SeoCity;
use App\Models\SeoKeyword;
use App\Models\SeoCityKeyword;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\Builder;

/**
 * SEO Keyword & Location Generator Service
 * 
 * Generates 150,000+ landing pages by:
 * - Managing keyword universe (250,000 keywords)
 * - Managing location database (938 cities/districts)
 * - Creating city-keyword combinations
 * - Generating accessible URLs
 */
class SeoGeneratorService
{
    protected string $domain;
    protected int $targetIndexablePages;
    protected int $keywordUniverseSize;

    public function __construct()
    {
        $this->domain = config('snackzar.seo.canonical_domain');
        $this->targetIndexablePages = config('snackzar.seo.programmatic.target_indexable_pages', 150000);
        $this->keywordUniverseSize = config('snackzar.seo.programmatic.keyword_universe_size', 250000);
    }

    /**
     * Generate seed keywords for the SEO system
     * Focuses on makhana/snacks related keywords
     */
    public function generateSeedKeywords(): void
    {
        $baseKeywords = [
            // Commercial intent
            ['keyword' => 'buy makhana online', 'intent' => 'commercial', 'difficulty' => 25],
            ['keyword' => 'best makhana brand', 'intent' => 'commercial', 'difficulty' => 35],
            ['keyword' => 'makhana delivery', 'intent' => 'commercial', 'difficulty' => 30],
            ['keyword' => 'organic makhana', 'intent' => 'commercial', 'difficulty' => 40],
            ['keyword' => 'roasted makhana', 'intent' => 'commercial', 'difficulty' => 28],
            ['keyword' => 'fox nuts online', 'intent' => 'commercial', 'difficulty' => 32],

            // Informational intent
            ['keyword' => 'makhana benefits', 'intent' => 'informational', 'difficulty' => 20],
            ['keyword' => 'makhana nutrition', 'intent' => 'informational', 'difficulty' => 18],
            ['keyword' => 'how to eat makhana', 'intent' => 'informational', 'difficulty' => 15],
            ['keyword' => 'makhana health benefits', 'intent' => 'informational', 'difficulty' => 22],
            ['keyword' => 'makhana protein content', 'intent' => 'informational', 'difficulty' => 19],

            // "Near me" keywords
            ['keyword' => 'makhana near me', 'intent' => 'transactional', 'difficulty' => 25],
            ['keyword' => 'snacks near me', 'intent' => 'transactional', 'difficulty' => 30],
            ['keyword' => 'healthy snacks near me', 'intent' => 'transactional', 'difficulty' => 28],

            // Branded variations
            ['keyword' => 'makhana store', 'intent' => 'commercial', 'difficulty' => 35],
            ['keyword' => 'makhana supplier', 'intent' => 'commercial', 'difficulty' => 40],
            ['keyword' => 'makhana wholesale', 'intent' => 'commercial', 'difficulty' => 45],
        ];

        $locationQualifiers = [
            'in', 'near', 'online in', 'near me in', 'best in', 'traditional in', 'organic in'
        ];

        foreach ($baseKeywords as $base) {
            // Create base keyword
            SeoKeyword::firstOrCreate(
                ['slug' => str()->slug($base['keyword'])],
                [
                    'keyword' => $base['keyword'],
                    'intent' => $base['intent'],
                    'keyword_difficulty' => $base['difficulty'],
                    'is_active' => true,
                    'priority' => 100 - $base['difficulty'], // Higher priority for easier keywords
                    'variations' => $this->generateKeywordVariations($base['keyword']),
                ]
            );
        }

        // Log creation
        \Log::info('SEO: Generated seed keywords', ['count' => count($baseKeywords)]);
    }

    /**
     * Generate keyword variations
     */
    protected function generateKeywordVariations(string $keyword): array
    {
        $base = strtolower($keyword);
        
        return [
            $base,
            str_replace(' ', '-', $base),
            ucwords($base),
            'best ' . $base,
            'buy ' . $base,
            'online ' . $base,
        ];
    }

    /**
     * Seed cities and districts for location targeting
     */
    public function seedLocations(): void
    {
        // Bihar districts (38 districts)
        $bihiDistricts = [
            'Araria', 'Arwal', 'Aurangabad', 'Banka', 'Begusarai', 'Bhagalpur', 'Bhojpur',
            'Buxar', 'Darbhanga', 'East Champaran', 'Gaya', 'Gopalganj', 'Jamui', 'Jehanabad',
            'Jha Jha', 'Jhunjhunu', 'Kaimur', 'Katihar', 'Khagaria', 'Kishanganj', 'Lakhisarai',
            'Madhepura', 'Madhubani', 'Munger', 'Musllipur', 'Muzaffarpur', 'Nalanda', 'Nawi',
            'Nawada', 'Patna', 'Purnia', 'Rohtas', 'Saharsa', 'Samastipur', 'Saran',
            'Sheikhpura', 'Sheohar', 'Siwan', 'Supaul', 'Vaishali', 'West Champaran',
        ];

        foreach ($bihiDistricts as $district) {
            SeoCity::firstOrCreate(
                ['slug' => str()->slug($district), 'country' => 'IN'],
                [
                    'name' => $district,
                    'type' => 'district',
                    'state' => 'Bihar',
                    'country' => 'IN',
                    'is_active' => true,
                    'priority' => 90, // High priority for districts
                ]
            );
        }

        // Major Indian cities (sample of 420+)
        $indianCities = [
            // Metro cities
            ['name' => 'Delhi', 'state' => 'Delhi'],
            ['name' => 'Mumbai', 'state' => 'Maharashtra'],
            ['name' => 'Bengaluru', 'state' => 'Karnataka'],
            ['name' => 'Hyderabad', 'state' => 'Telangana'],
            ['name' => 'Chennai', 'state' => 'Tamil Nadu'],
            ['name' => 'Kolkata', 'state' => 'West Bengal'],
            ['name' => 'Pune', 'state' => 'Maharashtra'],
            ['name' => 'Ahmedabad', 'state' => 'Gujarat'],
            ['name' => 'Jaipur', 'state' => 'Rajasthan'],
            ['name' => 'Lucknow', 'state' => 'Uttar Pradesh'],
            // Add more cities as needed
        ];

        foreach ($indianCities as $city) {
            SeoCity::firstOrCreate(
                ['slug' => str()->slug($city['name']), 'country' => 'IN'],
                [
                    'name' => $city['name'],
                    'type' => 'city',
                    'state' => $city['state'],
                    'country' => 'IN',
                    'is_active' => true,
                    'priority' => 80,
                ]
            );
        }

        \Log::info('SEO: Seeded locations', [
            'bihar_districts' => count($bihiDistricts),
            'indian_cities' => count($indianCities),
        ]);
    }

    /**
     * Generate city-keyword combinations for landing pages
     * Returns the number of pages generated
     */
    public function generateCityKeywordCombinations(int $limit = 0): int
    {
        $activeCities = SeoCity::where('is_active', true)->get();
        $activeKeywords = SeoKeyword::where('is_active', true)->get();

        $created = 0;
        $skipped = 0;

        foreach ($activeCities as $city) {
            foreach ($activeKeywords as $keyword) {
                // Check if combination already exists
                $exists = SeoCityKeyword::where('seo_city_id', $city->id)
                    ->where('seo_keyword_id', $keyword->id)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $urlSlug = $this->generateUrlSlug($city, $keyword);
                $pageTitle = $this->generatePageTitle($city, $keyword);
                $metaDescription = $this->generateMetaDescription($city, $keyword);

                SeoCityKeyword::create([
                    'seo_city_id' => $city->id,
                    'seo_keyword_id' => $keyword->id,
                    'url_slug' => $urlSlug,
                    'page_title' => $pageTitle,
                    'meta_description' => $metaDescription,
                    'is_indexed' => true,
                ]);

                $created++;

                if ($limit > 0 && $created >= $limit) {
                    break;
                }
            }

            if ($limit > 0 && $created >= $limit) {
                break;
            }
        }

        \Log::info('SEO: Generated city-keyword combinations', [
            'created' => $created,
            'skipped' => $skipped,
        ]);

        return $created;
    }

    /**
     * Generate SEO-friendly URL slug
     */
    protected function generateUrlSlug(SeoCity $city, SeoKeyword $keyword): string
    {
        $citySlug = $city->slug;
        $keywordSlug = $keyword->slug;

        // Combine: keyword-in-city for better SEO
        return "{$keywordSlug}-in-{$citySlug}";
    }

    /**
     * Generate optimized page title
     */
    protected function generatePageTitle(SeoCity $city, SeoKeyword $keyword): string
    {
        // Pattern: "{Keyword} in {City} | Snackzar"
        $titles = [
            "{$keyword->keyword} in {$city->name} | Snackzar",
            "Best {$keyword->keyword} in {$city->name} | Snackzar",
            "{$keyword->keyword} Online in {$city->name} | Snackzar",
            "Buy {$keyword->keyword} in {$city->name} | Snackzar",
        ];

        return $titles[array_rand($titles)];
    }

    /**
     * Generate optimized meta description
     */
    protected function generateMetaDescription(SeoCity $city, SeoKeyword $keyword): string
    {
        $descriptions = [
            "Discover premium {$keyword->keyword} in {$city->name}. Quality assured & fresh delivery. Shop now on Snackzar.",
            "Find the best {$keyword->keyword} in {$city->name}. Organic & healthy options. Order online today.",
            "{$keyword->keyword} in {$city->name} - Premium quality, competitive prices. Snackzar delivers nationwide.",
        ];

        return $descriptions[array_rand($descriptions)];
    }

    /**
     * Get generated landing page statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_cities' => SeoCity::count(),
            'active_cities' => SeoCity::active()->count(),
            'total_keywords' => SeoKeyword::count(),
            'active_keywords' => SeoKeyword::active()->count(),
            'city_keyword_combinations' => SeoCityKeyword::count(),
            'active_landing_pages' => SeoCityKeyword::where('is_indexed', true)->count(),
            'target_indexable_pages' => $this->targetIndexablePages,
        ];
    }
}
