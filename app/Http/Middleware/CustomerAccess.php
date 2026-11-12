<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user->hasRole('customer')) {
            return redirect('/dashboard')->with('error', 'Access denied.');
        }

        return $next($request);
    }
}
