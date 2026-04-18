<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LayoutHelper
{
    public static function getLayoutForRole()
    {
        $user = Auth::user();

        if (!$user) {
            return 'layouts.guest';
        }

        return match ($user->user_type) {
            'SuperAdmin', 'super_admin' => 'layouts.superadmin',
            'Director' => 'layouts.director',
            'Student' => 'layouts.student',
            'Applicant' => 'layouts.app',
            default => 'layouts.staff',
        };
        Session::put($sessionKey, $layout);
        Session::save();

        return $layout;
    }

    public static function redirectRouteByRole($user)
    {
        return match ($user->user_type) {
            'SuperAdmin', 'super_admin' => 'superadmin.dashboard',
            'Director' => 'director.dashboard',
            'Student' => 'student.dashboard',
            'Applicant' => 'applicant.dashboard',
            default => 'staff.dashboard',
        };
    }
}
