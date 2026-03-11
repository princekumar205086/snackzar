<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\AdminSellerService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AdminSellerService $adminService
    ) {}

    public function index(): JsonResponse
    {
        $stats = $this->adminService->getDashboardStats();

        return $this->success($stats);
    }
}
