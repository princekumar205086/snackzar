<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Modules\Shared\Services\CategoryService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Admin Categories
 *
 * APIs for admin category management (CRUD).
 */
class AdminCategoryController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    public function index(): JsonResponse
    {
        $categories = $this->categoryService->listActive();

        return $this->success($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'unique:categories,slug'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $category = $this->categoryService->store($data);

        return $this->created($category, 'Category created.');
    }

    public function update(Request $request, int $categoryId): JsonResponse
    {
        $category = Category::findOrFail($categoryId);

        $data = $request->validate([
            'parent_id' => ['nullable', 'exists:categories,id'],
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', "unique:categories,slug,{$categoryId}"],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);

        $category = $this->categoryService->update($category, $data);

        return $this->success($category, 'Category updated.');
    }

    public function destroy(int $categoryId): JsonResponse
    {
        $category = Category::findOrFail($categoryId);
        $this->categoryService->delete($category);

        return $this->noContent('Category deleted.');
    }
}
