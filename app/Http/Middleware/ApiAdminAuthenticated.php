<?php

namespace App\Http\Middleware;

use App\Support\ApiAdminToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiAdminAuthenticated
{
    public function __construct(
        private readonly ApiAdminToken $tokenService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $admin = $this->tokenService->resolve($token);

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->attributes->set('apiAdmin', $admin);

        return $next($request);
    }
}
