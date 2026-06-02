<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApplicationUser;
use App\Models\OtpCode;
use App\Support\ApiResponse;
use App\Support\ApiUserToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly ApiUserToken $tokenService,
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobileNumber' => ['required', 'string', 'min:9', 'max:20'],
        ]);

        $user = ApplicationUser::firstOrCreate(
            ['mobile_number' => $validated['mobileNumber']],
            ['is_active' => true]
        );

        if (config('ceylon.otp_bypass')) {
            $user->update(['last_login_at' => now()]);

            return $this->success([
                'token' => $this->tokenService->create($user),
                'user' => $this->formatUser($user),
                'otpSkipped' => true,
            ], 'Login successful (OTP bypass enabled)');
        }

        $otp = (string) random_int(100000, 999999);

        $otpRecord = OtpCode::create([
            'application_user_id' => $user->id,
            'otp_code' => $otp,
            'attempts' => 0,
            'expires_at' => now()->addMinutes(config('ceylon.otp_ttl_minutes', 5)),
        ]);

        $response = [
            'otpReference' => $otpRecord->id,
            'expiresAt' => $otpRecord->expires_at,
        ];

        return $this->success($response, 'OTP sent successfully');
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mobileNumber' => ['required', 'string', 'min:9', 'max:20'],
            'otp' => ['required', 'digits:6'],
        ]);

        $user = ApplicationUser::where('mobile_number', $validated['mobileNumber'])->first();
        if (! $user) {
            return $this->fail('User not found', 404);
        }

        /** @var OtpCode|null $otpRecord */
        $otpRecord = $user
            ->otps()
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otpRecord || Carbon::parse($otpRecord->expires_at)->isPast()) {
            return $this->fail('OTP has expired', 422);
        }

        if ($otpRecord->otp_code !== $validated['otp']) {
            $otpRecord->increment('attempts');
            return $this->fail('OTP verification failed', 422);
        }

        $otpRecord->update(['verified_at' => now()]);
        $user->update(['last_login_at' => now()]);

        return $this->success([
            'token' => $this->tokenService->create($user),
            'user' => $this->formatUser($user),
        ], 'Login successful');
    }

    public function emailLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user = ApplicationUser::query()
            ->where('email', $validated['email'])
            ->first();

        if ($user) {
            if (!$user->is_active) {
                return $this->fail('This account is currently inactive.', 422);
            }

            if (!$user->password || !Hash::check($validated['password'], $user->password)) {
                return $this->fail('Invalid email or password.', 422);
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

        return $this->success([
            'token' => $this->tokenService->create($user),
            'user' => $this->formatUser($user),
        ], 'Login successful');
    }

    public function me(Request $request): JsonResponse
    {
        /** @var ApplicationUser|null $user */
        $user = $request->attributes->get('apiUser');

        if (!$user) {
            return $this->fail('Unauthorized', 401);
        }

        return $this->success($this->formatUser($user));
    }

    private function formatUser(ApplicationUser $user): array
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'mobileNumber' => $user->mobile_number,
            'role' => $user->role ?? 'user',
            'activeState' => $user->is_active,
            'lastLoginAt' => $user->last_login_at,
        ];
    }
}
