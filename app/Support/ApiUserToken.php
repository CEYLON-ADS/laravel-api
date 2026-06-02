<?php

namespace App\Support;

use App\Models\ApplicationUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class ApiUserToken
{
    public function create(ApplicationUser $user): string
    {
        $timestamp = (string) now()->timestamp;
        $signature = $this->signature($user->id, $timestamp);

        return base64_encode(implode('|', [$user->id, $timestamp, $signature]));
    }

    public function resolve(?string $token): ?ApplicationUser
    {
        if (!$token) {
            return null;
        }

        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            return null;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 3) {
            return null;
        }

        [$userId, $timestamp, $signature] = $parts;
        if (!hash_equals($this->signature($userId, $timestamp), $signature)) {
            return null;
        }

        $issuedAt = Carbon::createFromTimestamp((int) $timestamp);
        if ($issuedAt->lt(now()->subDays(30))) {
            return null;
        }

        return ApplicationUser::query()
            ->whereKey($userId)
            ->where('is_active', true)
            ->first();
    }

    private function signature(string $userId, string $timestamp): string
    {
        return hash_hmac('sha256', $userId.'|'.$timestamp, (string) Config::get('app.key'));
    }
}
