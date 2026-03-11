<?php

namespace App\Modules\Shared\Services;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class BlogService
{
    public function listPublished(array $filters = []): LengthAwarePaginator
    {
        $query = BlogPost::published()->with('author:id,name,avatar');

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        return $query->latest('published_at')->paginate($filters['per_page'] ?? 10);
    }

    public function findBySlug(string $slug): ?BlogPost
    {
        $post = BlogPost::published()
            ->where('slug', $slug)
            ->with('author:id,name,avatar')
            ->first();

        if ($post) {
            $post->incrementViews();
        }

        return $post;
    }

    public function store(array $data): BlogPost
    {
        $data['slug'] = Str::slug($data['title']) . '-' . uniqid();

        if (isset($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return BlogPost::create($data);
    }

    public function update(BlogPost $post, array $data): BlogPost
    {
        if (isset($data['title']) && $data['title'] !== $post->title) {
            $data['slug'] = Str::slug($data['title']) . '-' . uniqid();
        }

        if (isset($data['status']) && $data['status'] === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);

        return $post->fresh();
    }

    public function delete(BlogPost $post): void
    {
        $post->delete();
    }

    public function listForAdmin(array $filters = []): LengthAwarePaginator
    {
        $query = BlogPost::with('author:id,name');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }
}
