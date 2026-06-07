<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Support\ApiAdminToken;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ApiAdminToken $tokenService,
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $adminCount = AdminUser::query()->count();
        $admin = AdminUser::query()
            ->where('username', $credentials['username'])
            ->first();

        if ($adminCount === 0) {
            $expectedUsername = (string) config('ceylon.admin.username');
            $expectedPassword = (string) config('ceylon.admin.password');

            if (
                $credentials['username'] !== $expectedUsername ||
                $credentials['password'] !== $expectedPassword
            ) {
                return $this->fail('Invalid admin credentials.', 401);
            }

            $admin = AdminUser::create([
                'username' => $expectedUsername,
                'password' => Hash::make($expectedPassword),
                'role' => 'super_admin',
                'is_active' => true,
            ]);
        }

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return $this->fail('Invalid admin credentials.', 401);
        }

        if (!$admin->is_active) {
            return $this->fail('This admin account is inactive.', 403);
        }

        return $this->success([
            'token' => $this->tokenService->create($admin),
            'admin' => $this->formatAdmin($admin),
        ], 'Login successful');
    }

    public function me(Request $request): JsonResponse
    {
        /** @var AdminUser|null $admin */
        $admin = $request->attributes->get('apiAdmin');

        if (!$admin) {
            return $this->fail('Unauthorized', 401);
        }

        return $this->success($this->formatAdmin($admin));
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->success(null, 'Logged out successfully');
    }

    public function changePassword(Request $request): JsonResponse
    {
        /** @var AdminUser|null $admin */
        $admin = $request->attributes->get('apiAdmin');
        if (!$admin) {
            return $this->fail('Unauthorized', 401);
        }

        $validated = $request->validate([
            'currentPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:6', 'different:currentPassword'],
        ]);

        if (!Hash::check($validated['currentPassword'], $admin->password)) {
            return $this->fail('Current password is incorrect.', 422);
        }

        $admin->update(['password' => Hash::make($validated['newPassword'])]);

        return $this->success(null, 'Password updated successfully');
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
