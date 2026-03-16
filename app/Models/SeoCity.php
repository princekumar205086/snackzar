<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class SeoCity extends Model
{
    protected $table = 'seo_cities';

    protected $fillable = [
        'name',
        'slug',
        'type',
        'state',
        'country',
        'latitude',
        'longitude',
        'population',
        'description',
        'canonical_url',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // Relationships
    public function keywords(): HasMany
    {
        return $this->hasMany(SeoCityKeyword::class, 'seo_city_id');
    }

    public function landingPages(): HasMany
    {
        return $this->hasMany(SeoLandingPage::class, 'seo_city_id');
    }

    // Query Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeInternational($query)
    {
        return $query->where('country', '!=', 'IN');
    }

    public function scopeIndia($query)
    {
        return $query->where('country', 'IN');
    }

    public function scopeDistricts($query)
    {
        return $query->where('type', 'district');
    }

    public function scopeCities($query)
    {
        return $query->where('type', 'city');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', '>=', 75)->orderBy('priority', 'desc');
    }

    // Accessors
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type === 'district' ? "{$this->name} (District)" : $this->name
        );
    }

    public function getCanonicalUrlAttribute()
    {
        $domain = config('snackzar.seo.canonical_domain');
        
        if ($this->canonical_url) {
            return $this->canonical_url;
        }

        // Generate based on type
        if ($this->type === 'district') {
            return "https://{$domain}/makhana-in-{$this->slug}";
        }

        return "https://{$domain}/buy-makhana-online-{$this->slug}";
    }
}
