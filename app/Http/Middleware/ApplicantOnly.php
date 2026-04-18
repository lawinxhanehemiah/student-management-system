<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApplicantOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Hakikisha mtu yupo logged in
        if (!Auth::check()) {
            return redirect()->route('applicant.login')->with('error', 'Please login first.');
        }

        // 2. Pata user ya sasa
        $user = Auth::user();

        // 3. Hakikisha ni applicant
        if ($user->user_type !== 'applicant') {
            // Kama si applicant, logout na upeleke kwenye login ya staff
            Auth::logout();
            return redirect()->route('login')->with('error', 'Access denied. Please use staff login.');
        }

        // 4. Endelea na request
        return $next($request);
    }
}