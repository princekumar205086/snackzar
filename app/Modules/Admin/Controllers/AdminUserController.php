<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\AdminUserService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Admin Users
 *
 * APIs for admin user management (list, detail, ban/activate users).
 */
class AdminUserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AdminUserService $userService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->list(
            $request->only(['role', 'search', 'status', 'per_page'])
        );

        return $this->success($users);
    }

    public function show(int $user): JsonResponse
    {
        $user = $this->userService->show($user);

        return $this->success($user);
    }

    public function updateStatus(Request $request, int $user): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:active,banned,suspended'],
        ]);

        $user = $this->userService->updateStatus($user, $data['status']);

        return $this->success($user, 'User status updated.');
    }
}
