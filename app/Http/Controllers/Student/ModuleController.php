<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Curriculum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ModuleController extends Controller
{
    public function registeredModules(Request $request)
    {
        $student = Student::where('user_id', Auth::id())->firstOrFail();
        
        // 🔁 Tumia current_level badala ya current_year
        $programmeId = $student->programme_id;
        $currentYear = $student->current_level ?? 1;  // ← mabadiliko hapa

        // Debug (weka comment baada ya kuhakikisha inafanya kazi)
        // \Log::info('Student programme: ' . $programmeId . ', Year: ' . $currentYear);

        $query = Curriculum::with(['module.department', 'programme'])
            ->where('programme_id', $programmeId)
            ->where('year', $currentYear)
            ->where('status', 'active');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('module', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('code', 'like', "%$search%");
            });
        }

        $curriculum = $query->orderBy('semester')
            ->orderBy('module_id')
            ->paginate($request->get('length', 10));

        $currentAcademicYear = $this->getCurrentAcademicYear();
        $lastLogin = Auth::user()->last_login_at ?? Carbon::now();
        $today = Carbon::now()->format('d M, Y');

        return view('student.registered-modules', compact(
            'curriculum', 'student', 'currentYear', 'currentAcademicYear', 'lastLogin', 'today'
        ));
    }

    private function getCurrentAcademicYear()
    {
        $year = Carbon::now()->year;
        return $year . '/' . ($year + 1);
    }
}