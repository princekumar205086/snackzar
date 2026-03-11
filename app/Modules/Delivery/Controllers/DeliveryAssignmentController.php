<?php

namespace App\Modules\Delivery\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Delivery\Services\DeliveryAssignmentService;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Delivery Assignments
 *
 * APIs for delivery partners to manage assignments (list, accept, update status, complete).
 */
class DeliveryAssignmentController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly DeliveryAssignmentService $assignmentService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $assignments = $this->assignmentService->listAssignments(
            $request->user(),
            $request->only(['status', 'per_page'])
        );

        return $this->success($assignments);
    }

    public function show(Request $request, int $assignment): JsonResponse
    {
        $assignment = $this->assignmentService->getAssignment($request->user(), $assignment);

        return $this->success($assignment);
    }

    public function accept(Request $request, int $assignment): JsonResponse
    {
        $assignment = $this->assignmentService->acceptAssignment($request->user(), $assignment);

        return $this->success($assignment, 'Assignment accepted.');
    }

    public function pickUp(Request $request, int $assignment): JsonResponse
    {
        $assignment = $this->assignmentService->pickUp($request->user(), $assignment);

        return $this->success($assignment, 'Order picked up.');
    }

    public function deliver(Request $request, int $assignment): JsonResponse
    {
        $assignment = $this->assignmentService->markDelivered($request->user(), $assignment);

        return $this->success($assignment, 'Order delivered.');
    }
}
