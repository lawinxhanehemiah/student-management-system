<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class RegisterController extends Controller
{
    /**
     * Show registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.appyregister');
    }

    /**
     * Handle applicant registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'terms' => 'required|accepted',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Create user as applicant
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'user_type' => 'applicant',
        ]);

        // Assign applicant role
        $applicantRole = Role::where('name', 'applicant')->first();
        if ($applicantRole) {
            $user->assignRole($applicantRole);
        }

        // Auto login after registration
        auth()->login($user);

        // Send welcome email (optional)
        // Mail::to($user->email)->send(new WelcomeEmail($user));

        return redirect()->route('applicant.dashboard')
            ->with('success', 'Account created successfully! You can now start your application.');
    }
}