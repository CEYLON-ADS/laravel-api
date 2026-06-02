<?php

namespace App\Http\Middleware;

use App\Models\ApplicationUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserPhoneAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->session()->get('application_user_id');
        if (! $userId) {
            return redirect()->route('user.login.form')
                ->with('status', 'Please login with your phone number before listing.');
        }

        $user = ApplicationUser::query()->find($userId);
        if (! $user || ! $user->is_active) {
            $request->session()->forget(['application_user_id', 'application_user_mobile', 'application_user_role']);

            return redirect()->route('user.login.form')
                ->with('status', 'Your session expired. Please login again.');
        }

        $request->session()->put('application_user_role', $user->role ?? 'user');

        if (! in_array($user->role ?? 'user', ['ads_agent', 'admin', 'super_admin'], true)) {
            return redirect()->route('home')
                ->with('status', 'Only ads agents can create listings.');
        }

        return $next($request);
    }
}
