<?php

namespace App\Helpers;

class RedirectHelper
{
    public static function byRole($user)
    {
        $role = $user->getRoleNames()->first();

        return match ($role) {
            'SuperAdmin' => 'superadmin.dashboard',
            'Director' => 'director.dashboard',
            'Principal' => 'principal.dashboard',
            'Deputy_Principal_Academics' => 'dp.academics.dashboard',
            'Deputy_Principal_Administration' => 'dp.admin.dashboard',
            'Head_of_Department' => 'hod.dashboard',
            'Tutor' => 'tutor.dashboard',
            'Examination_Officer' => 'examination.dashboard',
            'Dean_of_Students' => 'dean.students.dashboard',
            'Admission_Officer' => 'admission.dashboard',
            'Records_Officer' => 'records.dashboard',
            'Secretary' => 'secretary.dashboard',
            'Financial_Controller' => 'finance.dashboard',
            'Accountant' => 'accountant.dashboard',
            'Procurement_Officer' => 'procurement.dashboard',
            'ICT_Manager' => 'ict.dashboard',
            'HR_Manager' => 'hr.dashboard',
            'Librarian' => 'library.dashboard',
            'Estate_Manager' => 'estate.dashboard',
            'PR_Marketing_Officer' => 'pr.dashboard',
            'Quality_Assurance_Manager' => 'qa.dashboard',
            'Applicant' => 'application.form',
            'Student' => 'student.dashboard',
            default => 'login', // kama role haijatambuliwa
        };
    }
}
