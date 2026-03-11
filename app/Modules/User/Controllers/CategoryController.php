<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Services\CategoryService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

/**
 * @group Categories
 *
 * APIs for browsing product categories.
 */
class CategoryController extends Controller
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

    public function show(string $slug): JsonResponse
    {
        $category = $this->categoryService->findBySlug($slug);

        if (!$category) {
            return $this->error('Category not found.', 404);
        }

        return $this->success($category);
    }
}
