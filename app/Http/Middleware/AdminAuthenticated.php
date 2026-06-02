<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->session()->get('is_admin_authenticated', false)) {
            return redirect()->route('admin.login');
        }

        $adminId = $request->session()->get('admin_user_id');
        if (! $adminId) {
            $request->session()->forget(['is_admin_authenticated', 'admin_user_id', 'admin_role']);
            return redirect()->route('admin.login');
        }

        $admin = AdminUser::query()->find($adminId);
        if (! $admin || ! $admin->is_active) {
            $request->session()->forget(['is_admin_authenticated', 'admin_user_id', 'admin_role']);
            return redirect()->route('admin.login');
        }

        return $next($request);
    }
}
