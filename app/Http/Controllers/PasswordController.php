<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Helpers\RedirectHelper;

class PasswordController extends Controller
{
    // Show password change form
    public function showChangeForm()
    {
        return view('auth.password_change'); // resources/views/auth/password_change.blade.php
    }

    // Update password
    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => ['required','confirmed','min:8','regex:/[A-Z]/','regex:/[0-9]/'],
        ]);

        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->password),
            'must_change_password' => false // mark that password has been changed
        ]);

        // After changing password, redirect normally based on role
        return redirect()->route(RedirectHelper::byRole($user));
    }
}
