<?php

namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var AdminUser|null $admin */
        $admin = $request->attributes->get('apiAdmin');

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        if (count($roles) > 0 && !in_array($admin->role, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden: insufficient role',
            ], 403);
        }

        return $next($request);
    }
}
