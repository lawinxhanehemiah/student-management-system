<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserType
{
    public function handle(Request $request, Closure $next, $type)
    {
        if (!Auth::check()) {
            return redirect()->route('applicant.login');
        }

        if (Auth::user()->user_type !== $type) {
            abort(403, 'Unauthorized access');
        }

        return $next($request);
    }
}