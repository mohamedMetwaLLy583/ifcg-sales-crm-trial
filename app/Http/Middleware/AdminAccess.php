<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_active || !$user->hasRole('admin')) {
            return redirect('/')->with('error', 'Access denied.');
        }

        return $next($request);
    }
}
