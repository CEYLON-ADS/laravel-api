<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApplicationUser;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $search = $request->string('search')->toString() ?: $request->string('q')->toString();

        $users = ApplicationUser::query()
            ->withCount('advertisements')
            ->when($search !== '', fn ($query) => $query->where(function ($searchQuery) use ($search): void {
                $searchQuery
                    ->where('mobile_number', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            }))
            ->latest()
            ->paginate((int) $request->integer('size', 20));

        return $this->success($users);
    }

    public function show(ApplicationUser $user): JsonResponse
    {
        $user->loadCount('advertisements');

        return $this->success($user);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobileNumber' => ['required', 'string', 'min:9', 'max:20', 'unique:application_users,mobile_number'],
            'name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160', 'unique:application_users,email'],
            'role' => ['required', 'string', 'in:user,ads_agent'],
            'activeState' => ['nullable', 'boolean'],
        ]);

        $user = ApplicationUser::create([
            'mobile_number' => $validated['mobileNumber'],
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'is_active' => (bool) ($validated['activeState'] ?? false),
        ]);

        return $this->success($user, 'User created successfully.', 201);
    }

    public function updateRole(Request $request, ApplicationUser $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'string', 'in:user,ads_agent'],
        ]);

        $user->update(['role' => $validated['role']]);

        return $this->success($user->fresh(), 'User role updated.');
    }

    public function toggleActive(ApplicationUser $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);

        return $this->success($user->fresh(), 'User active state updated.');
    }

    public function destroy(ApplicationUser $user): JsonResponse
    {
        $user->delete();

        return $this->success(null, 'User deleted.');
    }
}
