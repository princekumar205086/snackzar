<?php

namespace App\Modules\Shared\Services;

use App\Models\SeoCity;
use App\Models\SeoKeyword;
use App\Models\SeoCityKeyword;
use App\Models\SeoLandingPage;

/**
 * SEO Page Renderer Service
 * 
 * Generates complete landing page content including:
 * - H1 headings
 * - Meta descriptions
 * - Body content
 * - FAQ sections
 * - JSON-LD schemas
 * - Internal links
 */
class SeoPageRendererService
{
    protected SeoMetaTagsService $metaTagsService;

    public function __construct()
    {
        $this->metaTagsService = new SeoMetaTagsService();
    }

    /**
     * Generate landing page for city-keyword combination
     */
    public function generateLandingPage(SeoCityKeyword $cityKeyword): array
    {
        $city = $cityKeyword->city;
        $keyword = $cityKeyword->keyword;

        $canonicalUrl = "https://" . config('snackzar.seo.canonical_domain') . "/{$cityKeyword->url_slug}";

        return [
            'seo' => [
                'title' => $cityKeyword->page_title,
                'description' => $cityKeyword->meta_description,
                'canonical' => $canonicalUrl,
                'ogTitle' => $cityKeyword->page_title,
                'ogDescription' => $cityKeyword->meta_description,
                'ogImage' => config('snackzar.seo.og_image', 'https://cdn.snackzar.com/og-image.jpg'),
                'robots' => 'index, follow',
            ],
            'location' => [
                'name' => $city->name,
                'slug' => $city->slug,
                'type' => $city->type,
                'state' => $city->state,
                'country' => $city->country,
            ],
            'keyword' => [
                'term' => $keyword->keyword,
                'slug' => $keyword->slug,
                'intent' => $keyword->intent,
            ],
            'content' => [
                'h1' => $this->generateH1($city, $keyword),
                'introduction' => $this->generateIntroduction($city, $keyword),
                'benefits' => $this->generateBenefits($city, $keyword),
                'localContent' => $this->generateLocalContent($city),
                'faq' => $this->generateFAQ($city, $keyword),
            ],
            'schema' => [
                'breadcrumb' => $this->generateBreadcrumbSchema($city, $keyword),
                'organization' => $this->generateOrganizationSchema(),
                'localBusiness' => $this->generateLocalBusinessSchema($city),
                'article' => $this->generateArticleSchema($city, $keyword),
            ],
            'internalLinks' => $this->generateInternalLinks($city, $keyword),
        ];
    }

    /**
     * Generate H1 heading
     */
    protected function generateH1(SeoCity $city, SeoKeyword $keyword): string
    {
        $templates = [
            "{$keyword->keyword} in {$city->name} - Best Quality & Fresh",
            "Buy {$keyword->keyword} Online in {$city->name}",
            "{$city->name}'s Finest {$keyword->keyword} - Premium Quality",
            "Get Premium {$keyword->keyword} Delivered in {$city->name}",
            "Top {$keyword->keyword} Suppliers in {$city->name}",
        ];

        return $templates[array_rand($templates)];
    }

    /**
     * Generate introduction content
     */
    protected function generateIntroduction(SeoCity $city, SeoKeyword $keyword): string
    {
        return "Welcome to Snackzar's {$keyword->keyword} collection in {$city->name}. We bring you the finest quality {$keyword->keyword} sourced directly from trusted suppliers. Whether you're looking for organic options or traditional varieties, we have everything to satisfy your cravings. Shop {$keyword->keyword} online in {$city->name} and enjoy fast delivery to your doorstep.";
    }

    /**
     * Generate benefits section content
     */
    protected function generateBenefits(SeoCity $city, SeoKeyword $keyword): array
    {
        return [
            'Fresh Quality: Sourced fresh and delivered to you',
            'Competitive Prices: Best prices in {$city->name}',
            'Certified Organic: 100% certified organic options',
            'Quick Delivery: Fast and reliable delivery service',
            'Trusted Supplier: Trusted by thousands in {$city->name}',
        ];
    }

    /**
     * Generate location-specific content
     */
    protected function generateLocalContent(SeoCity $city): string
    {
        $localText = "In {$city->name}, we understand the local taste preferences and quality standards. Our {$city->name} collection is specially curated to meet the unique needs of our customers in {$city->displayName}.";

        if ($city->state) {
            $localText .= " As a {$city->state} favorite, our products are sourced keeping regional preferences in mind.";
        }

        return $localText;
    }

    /**
     * Generate FAQ section
     */
    protected function generateFAQ(SeoCity $city, SeoKeyword $keyword): array
    {
        return [
            [
                'question' => "What is the best {$keyword->keyword} to buy in {$city->name}?",
                'answer' => "Our premium collection offers the best quality {$keyword->keyword} in {$city->name}. Each product is carefully selected and tested for quality. We recommend exploring our top-rated options for the best experience.",
            ],
            [
                'question' => "How quickly can you deliver {$keyword->keyword} in {$city->name}?",
                'answer' => "We offer fast delivery across {$city->name}. Most orders are delivered within 24-48 hours. Express delivery options are available for select areas.",
            ],
            [
                'question' => "Is organic {$keyword->keyword} available in {$city->name}?",
                'answer' => "Yes! We have a dedicated organic {$keyword->keyword} collection for health-conscious customers in {$city->name}. All organic products are certified and verified.",
            ],
            [
                'question' => "What is the price range for {$keyword->keyword} in {$city->name}?",
                'answer' => "Our {$keyword->keyword} prices in {$city->name} are competitive and affordable. Prices vary based on quality and packaging. Check our store for detailed pricing.",
            ],
            [
                'question' => "Can I return {$keyword->keyword} if I'm not satisfied?",
                'answer' => "Yes, we have a hassle-free return policy. If you're not satisfied with your {$keyword->keyword} order, you can return it within 7 days for a full refund.",
            ],
        ];
    }

    /**
     * Generate Breadcrumb schema
     */
    protected function generateBreadcrumbSchema(SeoCity $city, SeoKeyword $keyword): array
    {
        $domain = config('snackzar.seo.canonical_domain');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => "https://$domain",
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $keyword->keyword,
                    'item' => "https://$domain/shop/{$city->slug}",
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $city->name,
                    'item' => "https://$domain/" . str()->slug($keyword->keyword) . "-in-{$city->slug}",
                ],
            ],
        ];
    }

    /**
     * Generate Organization schema
     */
    protected function generateOrganizationSchema(): array
    {
        $domain = config('snackzar.seo.canonical_domain');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Snackzar',
            'url' => "https://$domain",
            'logo' => "https://$domain/logo.png",
            'sameAs' => [
                config('snackzar.seo.social_media.facebook'),
                config('snackzar.seo.social_media.twitter'),
                config('snackzar.seo.social_media.instagram'),
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'Customer Service',
                'telephone' => config('snackzar.seo.phone'),
                'email' => config('snackzar.support_email'),
            ],
        ];
    }

    /**
     * Generate LocalBusiness schema for location
     */
    protected function generateLocalBusinessSchema(SeoCity $city): array
    {
        $domain = config('snackzar.seo.canonical_domain');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => "Snackzar - {$city->name}",
            'url' => "https://$domain",
            'telephone' => config('snackzar.seo.phone'),
            'areaServed' => [
                '@type' => 'City',
                'name' => $city->name,
                'addressRegion' => $city->state,
                'addressCountry' => $city->country,
            ],
            'geo' => $city->latitude && $city->longitude ? [
                '@type' => 'GeoCoordinates',
                'latitude' => $city->latitude,
                'longitude' => $city->longitude,
            ] : null,
        ];
    }

    /**
     * Generate Article schema
     */
    protected function generateArticleSchema(SeoCity $city, SeoKeyword $keyword): array
    {
        $domain = config('snackzar.seo.canonical_domain');

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => "{$keyword->keyword} in {$city->name}",
            'description' => "Discover premium {$keyword->keyword} in {$city->name}",
            'author' => [
                '@type' => 'Organization',
                'name' => 'Snackzar',
                'url' => "https://$domain",
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Snackzar',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => "https://$domain/logo.png",
                ],
            ],
            'datePublished' => now()->toIso8601String(),
            'dateModified' => now()->toIso8601String(),
        ];
    }

    /**
     * Generate internal links
     */
    protected function generateInternalLinks(SeoCity $city, SeoKeyword $keyword): array
    {
        $domain = config('snackzar.seo.canonical_domain');
        $links = [];

        // Link to products
        $links[] = [
            'text' => "Browse {$keyword->keyword} Products",
            'url' => "https://$domain/shop/{$keyword->slug}",
            'type' => 'primary',
        ];

        // Link to city page
        if ($city->type === 'district') {
            $links[] = [
                'text' => "{$city->name} Store",
                'url' => "https://$domain/makhana-in-{$city->slug}",
                'type' => 'primary',
            ];
        }

        // Link to blog
        $links[] = [
            'text' => "Blog: Benefits of {$keyword->keyword}",
            'url' => "https://$domain/blog/{$keyword->slug}-benefits",
            'type' => 'related',
        ];

        return $links;
    }
}
