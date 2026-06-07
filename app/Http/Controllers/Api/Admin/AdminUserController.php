<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString() ?: $request->string('q')->toString();

        $admins = AdminUser::query()
            ->when($search !== '', fn ($query) => $query->where('username', 'like', '%'.$search.'%'))
            ->orderBy('username')
            ->paginate((int) $request->integer('size', 20));

        return $this->success($admins);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:120', 'unique:admin_users,username'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'string', 'in:super_admin,admin,ads_agent'],
            'activeState' => ['nullable', 'boolean'],
        ]);

        $admin = AdminUser::create([
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'is_active' => (bool) ($validated['activeState'] ?? true),
        ]);

        return $this->success($this->formatAdmin($admin), 'Admin user created.', 201);
    }

    public function updateRole(Request $request, AdminUser $admin): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:super_admin,admin,ads_agent'],
        ]);

        /** @var AdminUser|null $current */
        $current = $request->attributes->get('apiAdmin');
        if ($current && $admin->id === $current->id) {
            return $this->fail('You cannot change your own role.', 422);
        }

        $admin->update(['role' => $validated['role']]);

        return $this->success($this->formatAdmin($admin->fresh()), 'Admin role updated.');
    }

    public function updatePassword(Request $request, AdminUser $admin): JsonResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $admin->update(['password' => Hash::make($validated['password'])]);

        return $this->success($this->formatAdmin($admin->fresh()), 'Admin password updated.');
    }

    public function toggleActive(Request $request, AdminUser $admin): JsonResponse
    {
        /** @var AdminUser|null $current */
        $current = $request->attributes->get('apiAdmin');
        if ($current && $admin->id === $current->id) {
            return $this->fail('You cannot deactivate your own account.', 422);
        }

        $admin->update(['is_active' => !$admin->is_active]);

        return $this->success($this->formatAdmin($admin->fresh()), 'Admin active state updated.');
    }

    public function destroy(Request $request, AdminUser $admin): JsonResponse
    {
        /** @var AdminUser|null $current */
        $current = $request->attributes->get('apiAdmin');
        if ($current && $admin->id === $current->id) {
            return $this->fail('You cannot delete your own account.', 422);
        }

        $admin->delete();

        return $this->success(null, 'Admin user deleted.');
    }

    private function formatAdmin(AdminUser $admin): array
    {
        return [
            'id' => $admin->id,
            'username' => $admin->username,
            'role' => $admin->role,
            'activeState' => $admin->is_active,
        ];
    }
}
