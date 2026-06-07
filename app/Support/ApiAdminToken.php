<?php

namespace App\Support;

use App\Models\AdminUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class ApiAdminToken
{
    public function create(AdminUser $admin): string
    {
        $timestamp = (string) now()->timestamp;
        $signature = $this->signature($admin->id, $admin->role, $timestamp);

        return base64_encode(implode('|', [$admin->id, $admin->role, $timestamp, $signature]));
    }

    public function resolve(?string $token): ?AdminUser
    {
        if (!$token) {
            return null;
        }

        $decoded = base64_decode($token, true);
        if ($decoded === false) {
            return null;
        }

        $parts = explode('|', $decoded);
        if (count($parts) !== 4) {
            return null;
        }

        [$adminId, $role, $timestamp, $signature] = $parts;
        if (!hash_equals($this->signature($adminId, $role, $timestamp), $signature)) {
            return null;
        }

        $issuedAt = Carbon::createFromTimestamp((int) $timestamp);
        if ($issuedAt->lt(now()->subDays(7))) {
            return null;
        }

        return AdminUser::query()
            ->whereKey($adminId)
            ->where('role', $role)
            ->where('is_active', true)
            ->first();
    }

    private function signature(string $adminId, string $role, string $timestamp): string
    {
        return hash_hmac('sha256', $adminId.'|'.$role.'|'.$timestamp, (string) Config::get('app.key'));
    }
}
