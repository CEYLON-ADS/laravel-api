<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\ApplicationUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
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
                return back()
                    ->withErrors(['username' => 'Invalid admin credentials.'])
                    ->withInput($request->except('password'));
            }

            $admin = AdminUser::create([
                'username' => $expectedUsername,
                'password' => Hash::make($expectedPassword),
                'role' => 'super_admin',
                'is_active' => true,
            ]);
        }

        if (! $admin || ! Hash::check($credentials['password'], $admin->password)) {
            return back()
                ->withErrors(['username' => 'Invalid admin credentials.'])
                ->withInput($request->except('password'));
        }

        if (! $admin->is_active) {
            return back()
                ->withErrors(['username' => 'This admin account is inactive.'])
                ->withInput($request->except('password'));
        }

        $request->session()->regenerate();
        $request->session()->put('is_admin_authenticated', true);
        $request->session()->put('admin_user_id', $admin->id);
        $request->session()->put('admin_role', $admin->role);

        if ($admin->role === 'ads_agent') {
            $shadowUser = $this->ensureAdsAgentUser($admin);
            $request->session()->put('admin_application_user_id', $shadowUser->id);
            $request->session()->put('application_user_id', $shadowUser->id);
            $request->session()->put('application_user_mobile', $shadowUser->mobile_number);
            $request->session()->put('application_user_role', $shadowUser->role ?? 'ads_agent');

            return redirect()->route('admin.ads.index');
        }

        return redirect()->route('admin.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'is_admin_authenticated',
            'admin_user_id',
            'admin_role',
            'admin_application_user_id',
            'application_user_id',
            'application_user_mobile',
            'application_user_role',
        ]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function ensureAdsAgentUser(AdminUser $admin): ApplicationUser
    {
        $shadowMobile = 'ad'.substr(sha1($admin->username), 0, 18);

        return ApplicationUser::firstOrCreate(
            ['mobile_number' => $shadowMobile],
            [
                'name' => 'Ads Agent '.$admin->username,
                'role' => 'ads_agent',
                'is_active' => true,
            ]
        );
    }
}
