<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'blog_post_id',
        'title',
        'slug',
        'category',
        'cluster',
        'excerpt',
        'content',
        'featured_image',
        'image_source',
        'image_alt',
        'table_of_contents',
        'faq_items',
        'is_featured',
        'status',
        'published_at',
        'content_word_count',
    ];

    protected function casts(): array
    {
        return [
            'table_of_contents' => 'array',
            'faq_items' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class);
    }

    public function meta(): HasOne
    {
        return $this->hasOne(PostMeta::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
