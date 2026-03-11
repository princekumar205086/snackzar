<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Modules\Shared\Services\BlogService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Admin Blog
 *
 * APIs for admin blog post management (CRUD).
 */
class AdminBlogController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly BlogService $blogService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $posts = $this->blogService->listForAdmin($request->only(['status', 'per_page']));

        return $this->success($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featured_image' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'status' => 'in:draft,published',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $data['author_id'] = $request->user()->id;

        $post = $this->blogService->store($data);

        return $this->created($post);
    }

    public function show(int $id): JsonResponse
    {
        $post = BlogPost::with('author:id,name')->findOrFail($id);

        return $this->success($post);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'sometimes|string',
            'featured_image' => 'nullable|string|max:500',
            'category' => 'nullable|string|max:100',
            'tags' => 'nullable|array',
            'status' => 'in:draft,published,archived',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords' => 'nullable|string|max:255',
        ]);

        $post = $this->blogService->update($post, $data);

        return $this->success($post);
    }

    public function destroy(int $id): JsonResponse
    {
        $post = BlogPost::findOrFail($id);
        $this->blogService->delete($post);

        return $this->noContent();
    }
}
