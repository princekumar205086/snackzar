<?php

namespace App\Modules\Shared\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Structured Data Service
 * Generates JSON-LD schema markup for SEO optimization
 */
class StructuredDataService
{
    public function generateOrganizationSchema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'Snackzar',
            'alternateName' => 'Snackzar - Premium Makhana & Healthy Snacks',
            'url' => config('app.url'),
            'logo' => config('app.url') . '/images/logo.png',
            'description' => 'Buy premium makhana, fox nuts, and healthy snacks online. Direct from Bihar manufacturers. Fast delivery across India and globally.',
            'sameAs' => [
                'https://www.facebook.com/snackzar',
                'https://www.instagram.com/snackzar',
                'https://www.twitter.com/snackzar',
                'https://www.youtube.com/snackzar',
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'Customer Support',
                'telephone' => '+91-XXXXXX',
                'email' => 'support@snackzar.com',
                'areaServed' => ['IN', 'US', 'GB', 'AE', 'SG'],
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => 'Bihar, India',
                'addressCountry' => 'IN',
            ],
        ];
    }

    public function generateProductSchema(Model $product, $variants = null): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->name,
            'description' => $product->description ?? $product->short_description,
            'image' => $product->primaryImage?->url ?? config('app.url') . '/images/placeholder-product.svg',
            'brand' => [
                '@type' => 'Brand',
                'name' => config('snackzar.brand_name', 'Snackzar'),
            ],
            'productId' => $product->sku ?? $product->id,
            'sku' => $product->sku,
            'url' => route('products.show', $product->slug),
        ];

        // Add price if available
        if ($variants && count($variants) > 0) {
            $minPrice = $variants->min('price');
            $maxPrice = $variants->max('price');
            
            $schema['aggregateOffer'] = [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'INR',
                'lowPrice' => $minPrice,
                'highPrice' => $maxPrice,
                'offerCount' => count($variants),
            ];
        } elseif ($product->price) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'url' => route('products.show', $product->slug),
                'priceCurrency' => 'INR',
                'price' => $product->price,
                'availability' => $product->in_stock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            ];
        }

        // Add rating if available
        if ($product->avg_rating && $product->review_count) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => round($product->avg_rating, 1),
                'reviewCount' => $product->review_count,
                'bestRating' => '5',
                'worstRating' => '1',
            ];
        }

        return $schema;
    }

    public function generateBreadcrumbSchema(array $breadcrumbs): array
    {
        $itemListElement = [];
        foreach ($breadcrumbs as $index => $breadcrumb) {
            $itemListElement[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $breadcrumb['name'],
                'item' => $breadcrumb['url'] ?? null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $itemListElement,
        ];
    }

    public function generateFAQSchema(array $faqs): array
    {
        $mainEntity = [];
        foreach ($faqs as $faq) {
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => $faq['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $faq['answer'],
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ];
    }

    public function generateReviewSchema(Model $review): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Review',
            'reviewRating' => [
                '@type' => 'Rating',
                'ratingValue' => $review->rating,
                'bestRating' => '5',
                'worstRating' => '1',
            ],
            'reviewBody' => $review->comment ?? $review->title,
            'author' => [
                '@type' => 'Person',
                'name' => $review->user->name ?? 'Anonymous',
            ],
            'reviewDate' => $review->created_at->toIso8601String(),
        ];
    }

    public function generateArticleSchema(Model $article): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            '@id' => route('blog.show', $article->slug),
            'headline' => $article->title,
            'description' => $article->meta_description ?? $article->excerpt,
            'image' => $article->featured_image ?? config('app.url') . '/images/default-article.png',
            'datePublished' => $article->published_at->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $article->author->name ?? config('app.name'),
                'url' => config('app.url'),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('app.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => config('app.url') . '/images/logo.png',
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => route('blog.show', $article->slug),
            ],
        ];
    }

    public function generateLocalBusinessSchema(array $locationData): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'Snackzar - ' . ($locationData['name'] ?? 'Local Store'),
            'image' => config('app.url') . '/images/logo.png',
            'description' => $locationData['description'] ?? 'Premium makhana and healthy snacks delivery service',
            'url' => config('app.url'),
            'telephone' => config('snackzar.phone'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $locationData['address'] ?? '',
                'addressLocality' => $locationData['city'] ?? 'Bihar',
                'addressRegion' => $locationData['state'] ?? 'Bihar',
                'postalCode' => $locationData['postal_code'] ?? '',
                'addressCountry' => 'IN',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => $locationData['latitude'] ?? 0,
                'longitude' => $locationData['longitude'] ?? 0,
            ],
            'openingHoursSpecification' => [
                '@type' => 'OpeningHoursSpecification',
                'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'opens' => '08:00',
                'closes' => '22:00',
            ],
        ];
    }

    public function generateAggregateRatingSchema(
        float $ratingValue,
        int $ratingCount,
        string $bestRating = '5',
        string $worstRating = '1'
    ): array {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'AggregateRating',
            'ratingValue' => $ratingValue,
            'ratingCount' => $ratingCount,
            'bestRating' => $bestRating,
            'worstRating' => $worstRating,
        ];
    }

    public function generateCityLandingPageSchema(array $cityData): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => 'Snackzar - Buy Makhana Online in ' . $cityData['name'],
            'description' => 'Buy premium makhana and healthy snacks online in ' . $cityData['name'] . '. Direct delivery from Bihar manufacturer.',
            'image' => config('app.url') . '/images/logo.png',
            'url' => config('app.url') . '/buy-makhana-online-' . strtolower(str_replace(' ', '-', $cityData['name'])),
            'areaServed' => [
                '@type' => 'City',
                'name' => $cityData['name'],
                'addressCountry' => 'IN',
            ],
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $cityData['name'],
                'addressCountry' => 'IN',
            ],
        ];
    }

    public function renderJsonLd(array $schema): string
    {
        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }

    public function renderMultipleJsonLd(array $schemas): string
    {
        $output = '';
        foreach ($schemas as $schema) {
            $output .= $this->renderJsonLd($schema) . "\n";
        }
        return $output;
    }
}
