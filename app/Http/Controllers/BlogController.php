<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Modules\Shared\Services\BlogService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class BlogController extends Controller
{
    public function __construct(
        private readonly BlogService $blogService
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $posts = $this->blogService->listPublished($request->only(['category', 'search', 'per_page']));

        $categories = BlogPost::published()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->toArray();

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'filters' => $request->only(['category', 'search']),
            'categories' => $categories,
        ]);
    }

    public function show(string $slug): InertiaResponse
    {
        $post = $this->blogService->findBySlug($slug);

        if (!$post) {
            abort(404);
        }

        $relatedPosts = BlogPost::published()
            ->where('id', '!=', $post->id)
            ->where('category', $post->category)
            ->latest('published_at')
            ->limit(3)
            ->get(['id', 'title', 'slug', 'excerpt', 'featured_image', 'category']);

        $canonicalUrl = $post->canonical_url ?: route('blog.show', ['slug' => $post->slug]);

        $schemas = array_values(array_filter([
            $post->article_schema,
            $post->breadcrumb_schema,
            $post->faq_schema,
        ]));

        return Inertia::render('Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'canonicalUrl' => $canonicalUrl,
            'schemas' => $schemas,
        ]);
    }
}
