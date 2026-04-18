<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TutorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->role !== 'tutor') {
            abort(403);
        }

        return $next($request);
    }
}