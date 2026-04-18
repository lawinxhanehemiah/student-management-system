<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\RedirectHelper;

class AuthController extends Controller
{
    public function showLogin()
{
    // Kama tayari ame-login, mpe dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
}

public function login(Request $request)
{
    $request->validate([
        'login' => ['required'],
        'password' => ['required'],
    ]);

    $loginInput = $request->login;

    $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL)
        ? 'email'
        : 'registration_number';

    if (Auth::attempt([$field => $loginInput, 'password' => $request->password])) {
        // Hakikisha si applicant
        if (Auth::user()->user_type === 'applicant') {
            Auth::logout(); // logout immediately
            return back()->withErrors([
                'login' => 'Invalid credentials', // show generic error
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->must_change_password) {
            return redirect()->route('password.change.form');
        }

        return redirect()->route(RedirectHelper::byRole($user));
    }

    return back()->withErrors([
        'login' => 'Invalid credentials',
    ]);
}

    public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // RUDI KWENYE SYSTEM LOGIN
    return redirect()->route('login');
}
}
