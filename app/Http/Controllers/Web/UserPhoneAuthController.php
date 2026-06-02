<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ApplicationUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserPhoneAuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        return view('auth.phone-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = ApplicationUser::query()
            ->where('email', $validated['email'])
            ->first();

        if ($user) {
            if (! $user->is_active) {
                return back()->withErrors([
                    'email' => 'This account is currently inactive.',
                ])->withInput();
            }

            if (! $user->password || ! Hash::check($validated['password'], $user->password)) {
                return back()->withErrors([
                    'email' => 'Invalid email or password.',
                ])->withInput();
            }
        } else {
            $user = ApplicationUser::create([
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'mobile_number' => 'email_'.substr(sha1($validated['email']), 0, 18),
                'role' => 'user',
                'is_active' => true,
            ]);
        }

        $user->update(['last_login_at' => now()]);

        $request->session()->regenerate();
        $request->session()->put('application_user_id', $user->id);
        $request->session()->put('application_user_mobile', $user->mobile_number);
        $request->session()->put('application_user_role', $user->role ?? 'user');

        return $this->redirectAfterLogin($user)
            ->with('status', 'Login successful.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'application_user_id',
            'application_user_mobile',
            'application_user_role',
        ]);

        return redirect()->route('home')->with('status', 'You have been logged out.');
    }

    private function redirectAfterLogin(ApplicationUser $user): RedirectResponse
    {
        $role = $user->role ?? 'user';
        if (in_array($role, ['ads_agent', 'admin', 'super_admin'], true)) {
            return redirect()->intended(route('ads.create'));
        }

        return redirect()->intended(route('home'));
    }
}
