<?php

namespace App\Http\Middleware;

use App\Support\ApiUserToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiUserAuthenticated
{
    public function __construct(
        private readonly ApiUserToken $tokenService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $user = $this->tokenService->resolve($token);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $request->attributes->set('apiUser', $user);

        return $next($request);
    }
}
