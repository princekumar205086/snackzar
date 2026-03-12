<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\User;
use App\Modules\Shared\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * @group Admin Coupons
 *
 * Enterprise-level coupon management: public, individual, bulk, and enterprise coupons.
 */
class AdminCouponController extends Controller
{
    use ApiResponse;

    // ── List ──────────────────────────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        $query = Coupon::with('owner:id,name,email')
            ->withCount('assignedUsers');

        if ($request->filled('scope')) {
            $query->where('scope', $request->scope);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) =>
                $q->where('code', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%")
                  ->orWhere('label', 'like', "%{$s}%")
            );
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        $coupons = $query->latest()->paginate($request->input('per_page', 15));

        return $this->success($coupons);
    }

    // ── Show ──────────────────────────────────────────────────────────────────

    public function show(int $id): JsonResponse
    {
        $coupon = Coupon::with(['owner:id,name,email', 'assignedUsers:id,name,email'])
            ->findOrFail($id);

        return $this->success($coupon);
    }

    // ── Create ────────────────────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'               => ['nullable', 'string', 'max:32', 'unique:coupons,code'],
            'scope'              => ['required', 'in:public,individual,bulk,enterprise'],
            'type'               => ['required', 'in:percent,flat'],
            'value'              => ['required', 'numeric', 'min:0.01'],
            'max_discount'       => ['nullable', 'numeric', 'min:0'],
            'min_order_amount'   => ['nullable', 'numeric', 'min:0'],
            'max_uses'           => ['nullable', 'integer', 'min:0'],
            'max_uses_per_user'  => ['nullable', 'integer', 'min:0'],
            'expires_at'         => ['nullable', 'date', 'after:now'],
            'is_active'          => ['boolean'],
            'description'        => ['nullable', 'string', 'max:255'],
            'label'              => ['nullable', 'string', 'max:100'],
            'prefix'             => ['nullable', 'string', 'max:10', 'alpha_num'],
            // Individual coupon
            'user_id'            => ['nullable', 'exists:users,id', Rule::requiredIf(fn () => $request->scope === 'individual')],
        ]);

        // Auto-generate code if not provided
        if (empty($data['code'])) {
            $prefix = strtoupper($data['prefix'] ?? 'SNACK');
            $data['code'] = Coupon::generateCode($prefix);
        } else {
            $data['code'] = strtoupper(trim($data['code']));
        }

        $coupon = Coupon::create($data);

        return $this->success($coupon->load('owner:id,name,email'), 'Coupon created successfully.', 201);
    }

    // ── Update ────────────────────────────────────────────────────────────────

    public function update(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);

        $data = $request->validate([
            'code'               => ['nullable', 'string', 'max:32', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'scope'              => ['sometimes', 'in:public,individual,bulk,enterprise'],
            'type'               => ['sometimes', 'in:percent,flat'],
            'value'              => ['sometimes', 'numeric', 'min:0.01'],
            'max_discount'       => ['nullable', 'numeric', 'min:0'],
            'min_order_amount'   => ['nullable', 'numeric', 'min:0'],
            'max_uses'           => ['nullable', 'integer', 'min:0'],
            'max_uses_per_user'  => ['nullable', 'integer', 'min:0'],
            'expires_at'         => ['nullable', 'date'],
            'is_active'          => ['boolean'],
            'description'        => ['nullable', 'string', 'max:255'],
            'label'              => ['nullable', 'string', 'max:100'],
            'user_id'            => ['nullable', 'exists:users,id'],
        ]);

        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }

        $coupon->update($data);

        return $this->success($coupon->fresh('owner:id,name,email'), 'Coupon updated successfully.');
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return $this->success(null, 'Coupon deleted successfully.');
    }

    // ── Toggle active ─────────────────────────────────────────────────────────

    public function toggleActive(int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        return $this->success($coupon, $coupon->is_active ? 'Coupon activated.' : 'Coupon deactivated.');
    }

    // ── Assign to single user ─────────────────────────────────────────────────

    public function assignToUser(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $coupon->assignedUsers()->syncWithoutDetaching([
            $data['user_id'] => ['is_active' => true, 'used_count' => 0],
        ]);

        $user = User::find($data['user_id']);

        return $this->success(
            ['coupon' => $coupon->code, 'user' => $user->only(['id', 'name', 'email'])],
            "Coupon assigned to {$user->name}."
        );
    }

    // ── Revoke from single user ───────────────────────────────────────────────

    public function revokeFromUser(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);

        $request->validate(['user_id' => ['required', 'exists:users,id']]);

        $coupon->assignedUsers()->updateExistingPivot($request->user_id, ['is_active' => false]);

        return $this->success(null, 'Coupon revoked from user.');
    }

    // ── Bulk assign to multiple users ─────────────────────────────────────────

    public function bulkAssign(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);

        $data = $request->validate([
            'user_ids'   => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $pivotData = array_fill_keys(
            $data['user_ids'],
            ['is_active' => true, 'used_count' => 0]
        );

        $coupon->assignedUsers()->syncWithoutDetaching($pivotData);

        return $this->success(
            ['coupon' => $coupon->code, 'assigned_count' => count($data['user_ids'])],
            count($data['user_ids']) . ' users assigned successfully.'
        );
    }

    // ── Bulk assign by role/filter ────────────────────────────────────────────

    public function bulkAssignByFilter(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);

        $data = $request->validate([
            'filter'          => ['required', 'in:all,role,no_orders,registered_after'],
            'role'            => ['nullable', 'string'],
            'registered_after'=> ['nullable', 'date'],
        ]);

        $query = User::query();

        match ($data['filter']) {
            'role'              => $query->role($data['role'] ?? 'user'),
            'no_orders'         => $query->whereDoesntHave('orders'),
            'registered_after'  => $query->where('created_at', '>=', $data['registered_after']),
            default             => null,
        };

        $userIds = $query->pluck('id')->toArray();

        $pivotData = array_fill_keys($userIds, ['is_active' => true, 'used_count' => 0]);
        $coupon->assignedUsers()->syncWithoutDetaching($pivotData);

        return $this->success(
            ['coupon' => $coupon->code, 'assigned_count' => count($userIds)],
            count($userIds) . ' users assigned via filter.'
        );
    }

    // ── Generate bulk unique coupon codes ─────────────────────────────────────

    public function bulkGenerate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'count'              => ['required', 'integer', 'min:1', 'max:500'],
            'scope'              => ['required', 'in:public,individual,bulk,enterprise'],
            'type'               => ['required', 'in:percent,flat'],
            'value'              => ['required', 'numeric', 'min:0.01'],
            'max_discount'       => ['nullable', 'numeric', 'min:0'],
            'min_order_amount'   => ['nullable', 'numeric', 'min:0'],
            'max_uses'           => ['nullable', 'integer', 'min:0'],
            'max_uses_per_user'  => ['nullable', 'integer', 'min:0'],
            'expires_at'         => ['nullable', 'date', 'after:now'],
            'description'        => ['nullable', 'string', 'max:255'],
            'label'              => ['nullable', 'string', 'max:100'],
            'prefix'             => ['nullable', 'string', 'max:10', 'alpha_num'],
        ]);

        $count  = $data['count'];
        $prefix = strtoupper($data['prefix'] ?? 'BULK');
        unset($data['count'], $data['prefix']);

        $created = [];
        for ($i = 0; $i < $count; $i++) {
            $created[] = Coupon::create(array_merge($data, [
                'code'       => Coupon::generateCode($prefix),
                'used_count' => 0,
                'is_active'  => true,
            ]));
        }

        return $this->success(
            ['count' => count($created), 'codes' => array_column($created, 'code')],
            "{$count} coupons generated successfully.",
            201
        );
    }

    // ── Get assigned users for a coupon ───────────────────────────────────────

    public function assignedUsers(int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);

        $users = $coupon->assignedUsers()
            ->select('users.id', 'users.name', 'users.email')
            ->paginate(20);

        return $this->success($users);
    }

    // ── Stats ─────────────────────────────────────────────────────────────────

    public function stats(): JsonResponse
    {
        $stats = [
            'total'       => Coupon::count(),
            'active'      => Coupon::where('is_active', true)->count(),
            'expired'     => Coupon::whereNotNull('expires_at')->where('expires_at', '<', now())->count(),
            'by_scope'    => Coupon::selectRaw('scope, count(*) as count')->groupBy('scope')->pluck('count', 'scope'),
            'total_redemptions' => Coupon::sum('used_count'),
            'top_used'    => Coupon::orderByDesc('used_count')->take(5)->get(['id', 'code', 'used_count', 'description']),
        ];

        return $this->success($stats);
    }
}
