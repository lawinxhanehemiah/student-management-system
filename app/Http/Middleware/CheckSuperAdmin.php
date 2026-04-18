<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Using Spatie's hasRole method
        if (!Auth::user()->hasRole('superadmin')) {
            abort(403, 'Unauthorized access. Superadmin privileges required.');
        }

        return $next($request);
    }
}