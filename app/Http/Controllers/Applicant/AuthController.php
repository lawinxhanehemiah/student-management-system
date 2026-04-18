<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class AuthController extends Controller
{
    // Show applicant registration form
    public function showRegister()
    {
        return view('applicant.auth.register');
    }

    // Handle applicant registration
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => 'applicant',
        ]);

        // Auto-login after registration
        Auth::login($user);

        // Redirect to application flow
        return $this->redirectToApplicationFlow();
    }

    // Show applicant login form
    public function showLogin()
    {
        return view('applicant.auth.login');
    }

    // Handle applicant login
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'user_type' => 'applicant'
        ])) {
            $request->session()->regenerate();

            // Redirect based on application status
            return $this->redirectToApplicationFlow();
        }

        return back()->withErrors([
            'email' => 'Invalid credentials',
        ]);
    }

    /**
     * Redirect applicant to appropriate page based on application status
     */
    private function redirectToApplicationFlow()
    {
        $user = Auth::user();
        
        // Check if user has any submitted applications
        $hasSubmittedApplications = DB::table('applications')
            ->where('user_id', $user->id)
            ->where('status', '!=', 'draft')
            ->where('status', '!=', 'cancelled')
            ->exists();

        if (!$hasSubmittedApplications) {
            // Check if user has a draft application
            $hasDraftApplication = DB::table('applications')
                ->where('user_id', $user->id)
                ->where('status', 'draft')
                ->exists();

            if ($hasDraftApplication) {
                // Redirect to continue draft application
                return redirect()->route('applicant.application.form')
                    ->with('info', 'Continue your draft application');
            } else {
                // Redirect to start new application
                return redirect()->route('applicant.application.start')
                    ->with('success', 'Welcome! Please start your application');
            }
        }

        // User has submitted applications, redirect to dashboard
        return redirect()->route('applicant.dashboard')
            ->with('success', 'Welcome back!');
    }

    // Handle applicant logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('applicant.login')->with('success', 'Logged out successfully.');
    }

    // Show applicant profile
    public function showProfile()
    {
        $user = Auth::user();
        return view('applicant.auth.profile', compact('user'));
    }

    // Update applicant profile
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'current_password' => 'required_with:password|current_password',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        $user->update($updateData);

        return back()->with('success', 'Profile updated successfully.');
    }
}