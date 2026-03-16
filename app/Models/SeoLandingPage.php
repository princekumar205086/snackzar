<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoLandingPage extends Model
{
    protected $table = 'seo_landing_pages';

    protected $fillable = [
        'seo_city_keyword_id',
        'type',
        'page_name',
        'url_path',
        'page_title',
        'meta_description',
        'h1_heading',
        'content',
        'faq',
        'breadcrumbs',
        'internal_links',
        'image_url',
        'is_active',
        'is_indexed',
        'indexed_at',
        'view_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_indexed' => 'boolean',
        'indexed_at' => 'datetime',
        'faq' => 'array',
        'breadcrumbs' => 'array',
        'internal_links' => 'array',
    ];

    // Relationships
    public function cityKeyword(): BelongsTo
    {
        return $this->belongsTo(SeoCityKeyword::class, 'seo_city_keyword_id');
    }

    // Query Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeIndexed($query)
    {
        return $query->where('is_indexed', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeWithFAQ($query)
    {
        return $query->whereNotNull('faq');
    }

    public function scopeMostViewed($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    public function scopeRecentlyIndexed($query)
    {
        return $query->orderBy('indexed_at', 'desc');
    }

    // Accessors
    public function getCanonicalUrl(): string
    {
        $domain = config('snackzar.seo.canonical_domain');
        return "https://{$domain}{$this->url_path}";
    }

    public function getFAQSchema(): ?array
    {
        if (!$this->faq) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => collect($this->faq)->map(function ($item) {
                return [
                    '@type' => 'Question',
                    'name' => $item['question'] ?? '',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'] ?? '',
                    ],
                ];
            })->values()->all(),
        ];
    }

    public function getBreadcrumbSchema(): ?array
    {
        if (!$this->breadcrumbs) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => collect($this->breadcrumbs)->map(function ($item, $index) {
                return [
                    '@type' => 'ListItem',
                    'position' => $index + 1,
                    'name' => $item['name'] ?? '',
                    'item' => $item['url'] ?? '',
                ];
            })->values()->all(),
        ];
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
