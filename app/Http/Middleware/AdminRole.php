<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $currentRole = (string) $request->session()->get('admin_role', '');
        if (! $currentRole || (count($roles) > 0 && ! in_array($currentRole, $roles, true))) {
            return redirect()->route('admin.ads.index')
                ->with('status', 'You do not have permission to access that section.');
        }

        return $next($request);
    }
}
