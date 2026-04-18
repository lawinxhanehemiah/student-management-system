<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function redirect()
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unafaa ku-login');
        }

        $role = $user->getRoleNames()->first();

        return match ($role) {

            // ===== SYSTEM =====
            'SuperAdmin' => redirect()->route('superadmin.dashboard'),

            // ===== TOP MANAGEMENT =====
            'Director'   => redirect()->route('director.dashboard'),
            'Principal'  => redirect()->route('principal.dashboard'),

            // ===== DEPUTY PRINCIPALS =====
            'Deputy_Principal_Academics' =>
                redirect()->route('dp.academics.dashboard'),

            'Deputy_Principal_Administration' =>
                redirect()->route('dp.admin.dashboard'),

            // ===== ACADEMIC STRUCTURE =====
            'Head_of_Department' =>
                redirect()->route('hod.dashboard'),

            'Tutor' =>
                redirect()->route('tutor.dashboard'),

            'Examination_Officer' =>
                redirect()->route('examination.dashboard'),

            'Dean_of_Students' =>
                redirect()->route('dean.students.dashboard'),

            // ===== ADMINISTRATION =====
            'Admission_Officer' =>
                redirect()->route('admission.dashboard'),

            'Records_Officer' =>
                redirect()->route('records.dashboard'),

            'Secretary' =>
                redirect()->route('secretary.dashboard'),

            // ===== FINANCE & PROCUREMENT =====
            'Financial_Controller' =>
                redirect()->route('finance.dashboard'),

            'Accountant' =>
                redirect()->route('accountant.dashboard'),

            'Procurement_Officer' =>
                redirect()->route('procurement.dashboard'),

            // ===== SUPPORT SERVICES =====
            'ICT_Manager' =>
                redirect()->route('ict.dashboard'),

            'HR_Manager' =>
                redirect()->route('hr.dashboard'),

            'Librarian' =>
                redirect()->route('library.dashboard'),

            'Estate_Manager' =>
                redirect()->route('estate.dashboard'),

            'PR_Marketing_Officer' =>
                redirect()->route('pr.dashboard'),

            'Quality_Assurance_Manager' =>
                redirect()->route('qa.dashboard'),

                ' Applicant' =>
                redirect()->route('applicant.dashboard'),

                

            // ===== STUDENT =====
            'Student' =>
                redirect()->route('student.dashboard'),

            // ===== SAFETY =====
            default => abort(403, 'Role haijatambuliwa kwenye mfumo'),
        };
    }
}
