<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChangeController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/[A-Z]/',   // uppercase
                'regex:/[a-z]/',   // lowercase
                'regex:/[0-9]/',   // number
                'regex:/[@$!%*#?&]/', // special char
            ],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
            'password_changed_at' => now(),
        ]);

        return redirect()->intended('/dashboard');
    }
}
