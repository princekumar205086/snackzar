<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoKeyword extends Model
{
    protected $table = 'seo_keywords';

    protected $fillable = [
        'keyword',
        'slug',
        'variations',
        'search_volume',
        'keyword_difficulty',
        'intent',
        'description',
        'page_count',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'variations' => 'array',
    ];

    // Relationships
    public function cityKeywords(): HasMany
    {
        return $this->hasMany(SeoCityKeyword::class, 'seo_keyword_id');
    }

    // Query Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByIntent($query, $intent)
    {
        return $query->where('intent', $intent);
    }

    public function scopeHighVolume($query)
    {
        return $query->where('search_volume', '>=', 1000);
    }

    public function scopeEasy($query)
    {
        return $query->where('keyword_difficulty', '<', 30);
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 75)->orderBy('priority', 'desc');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')->orderBy('search_volume', 'desc');
    }
}
