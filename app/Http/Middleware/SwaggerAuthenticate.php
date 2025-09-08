<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SwaggerAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->hasRole('admin')) {
            return redirect()->route('swagger.login')->withErrors(['email' => 'You do not have permission to access this area']);
        }

        return $next($request);
    }
}
