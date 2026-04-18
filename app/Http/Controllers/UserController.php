<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:SuperAdmin');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string',
            'user_type' => 'required|in:staff,student,applicant',
            'role'      => 'required|exists:roles,name',

            // email only for staff/applicant
            'email'     => 'nullable|email|unique:users,email',

            // reg no only for student
            'registration_number' => 'nullable|unique:users,registration_number',

            'password'  => 'required|min:6',
        ]);

        /**
         * =========================
         * USER TYPE RULES
         * =========================
         */
        if ($request->user_type === 'student') {
            if (!$request->registration_number) {
                return back()->withErrors([
                    'registration_number' => 'Registration number required for student'
                ]);
            }
        }

        if ($request->user_type !== 'student') {
            if (!$request->email) {
                return back()->withErrors([
                    'email' => 'Email required for staff/applicant'
                ]);
            }
        }

        /**
         * =========================
         * CREATE USER
         * =========================
         */
        $user = User::create([
            'name' => $request->name,
            'email' => $request->user_type === 'student' ? null : $request->email,
            'registration_number' => $request->user_type === 'student'
                ? $request->registration_number
                : null,
            'password' => Hash::make($request->password),
            'user_type' => $request->user_type,
        ]);

        /**
         * =========================
         * ASSIGN ROLE
         * =========================
         */
        $user->assignRole($request->role);

        return redirect()->back()->with(
            'success',
            'User created successfully as ' . $request->user_type
        );
    }
}
