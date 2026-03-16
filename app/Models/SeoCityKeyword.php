<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoCityKeyword extends Model
{
    protected $table = 'seo_city_keyword';

    protected $fillable = [
        'seo_city_id',
        'seo_keyword_id',
        'url_slug',
        'page_title',
        'meta_description',
        'content_outline',
        'content_word_count',
        'has_faq',
        'has_schema',
        'is_indexed',
        'last_indexed_at',
        'view_count',
    ];

    protected $casts = [
        'has_faq' => 'boolean',
        'has_schema' => 'boolean',
        'is_indexed' => 'boolean',
        'last_indexed_at' => 'datetime',
        'content_outline' => 'array',
    ];

    // Relationships
    public function city(): BelongsTo
    {
        return $this->belongsTo(SeoCity::class, 'seo_city_id');
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(SeoKeyword::class, 'seo_keyword_id');
    }

    // Query Scopes
    public function scopeActive($query)
    {
        return $query->where('is_indexed', true);
    }

    public function scopeWithFAQ($query)
    {
        return $query->where('has_faq', true);
    }

    public function scopeWithSchema($query)
    {
        return $query->where('has_schema', true);
    }

    public function scopeByCity($query, $cityId)
    {
        return $query->where('seo_city_id', $cityId);
    }

    public function scopeByKeyword($query, $keywordId)
    {
        return $query->where('seo_keyword_id', $keywordId);
    }

    public function scopeMostViewed($query)
    {
        return $query->orderBy('view_count', 'desc');
    }

    public function scopeRecentlyIndexed($query)
    {
        return $query->orderBy('last_indexed_at', 'desc');
    }

    // Canonical URL
    public function getCanonicalUrl(): string
    {
        $domain = config('snackzar.seo.canonical_domain');
        return "https://{$domain}/{$this->url_slug}";
    }

    // Get full landing page data
    public function getPageData(): array
    {
        return [
            'title' => $this->page_title,
            'description' => $this->meta_description,
            'url' => $this->getCanonicalUrl(),
            'city' => $this->city,
            'keyword' => $this->keyword,
            'outline' => $this->content_outline,
        ];
    }
}
