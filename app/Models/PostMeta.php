<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostMeta extends Model
{
    use HasFactory;

    protected $table = 'post_meta';

    protected $fillable = [
        'post_id',
        'meta_title',
        'meta_description',
        'canonical_url',
        'article_schema',
        'breadcrumb_schema',
        'faq_schema',
    ];

    protected function casts(): array
    {
        return [
            'article_schema' => 'array',
            'breadcrumb_schema' => 'array',
            'faq_schema' => 'array',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
