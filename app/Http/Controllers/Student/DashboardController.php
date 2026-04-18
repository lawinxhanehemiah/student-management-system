<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Invoice;
use App\Models\FeeTransaction;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get last login time
     */
    private function getLastLogin()
    {
        $user = Auth::user();
        $lastLogin = $user->last_login_at;
        
        if ($lastLogin) {
            return date('d M, Y H:i:s', strtotime($lastLogin));
        }
        
        return 'First login';
    }

    /**
     * Get current academic year
     */
    private function getCurrentAcademicYear()
    {
        $currentYear = AcademicYear::where('is_active', true)
            ->orWhere('status', 'active')
            ->first();
        
        if ($currentYear) {
            return $currentYear->name;
        }
        
        return date('Y') . '/' . (date('Y') + 1);
    }

    /**
     * Get academic progress data
     */
    private function getAcademicProgress($student)
    {
        $ntaLevel = $student->current_level ?? 1;
        
        // Calculate progress percentage
        $totalLevels = $student->programme->duration ?? 3;
        $progressPercentage = ($ntaLevel / $totalLevels) * 100;
        
        // Determine year of study
        if ($ntaLevel <= 4) {
            $yearOfStudy = '1st Year';
            $yearNumber = 1;
        } elseif ($ntaLevel == 5) {
            $yearOfStudy = '2nd Year';
            $yearNumber = 2;
        } else {
            $yearOfStudy = '3rd Year';
            $yearNumber = 3;
        }
        
        // Determine study level
        if ($ntaLevel <= 3) {
            $studyLevel = 'Certificate';
        } else {
            $studyLevel = 'Diploma';
        }
        
        return [
            'nta_level' => $ntaLevel,
            'total_levels' => $totalLevels,
            'progress_percentage' => round($progressPercentage),
            'year_of_study' => $yearOfStudy,
            'year_number' => $yearNumber,
            'study_level' => $studyLevel,
            'current_semester' => $student->current_semester ?? 1,
            'gpa' => $student->cumulative_gpa ?? 'N/A',
            'academic_standing' => $student->academic_standing ?? 'Good',
        ];
    }

    /**
     * Get financial summary
     */
    private function getFinancialSummary($student)
    {
        $invoices = Invoice::where('student_id', $student->id)->get();
        
        $totalInvoiced = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
        $totalBalance = $invoices->sum('balance');
        
        $paymentPercentage = $totalInvoiced > 0 ? ($totalPaid / $totalInvoiced) * 100 : 0;
        
        return [
            'total_invoiced' => number_format($totalInvoiced, 0),
            'total_paid' => number_format($totalPaid, 0),
            'total_balance' => number_format($totalBalance, 0),
            'payment_percentage' => round($paymentPercentage),
            'is_cleared' => $student->fee_cleared ?? false,
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($student)
    {
        $activities = collect();
        
        // Get recent transactions
        $transactions = FeeTransaction::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        
        foreach ($transactions as $transaction) {
            $activities->push((object)[
                'type' => 'payment',
                'title' => 'Payment Made',
                'description' => 'TZS ' . number_format($transaction->credit, 0) . ' payment recorded',
                'time' => $transaction->created_at->diffForHumans(),
                'icon' => 'credit-card',
                'color' => 'success'
            ]);
        }
        
        // Add login activity
        $user = Auth::user();
        if ($user->last_login_at) {
            $activities->push((object)[
                'type' => 'login',
                'title' => 'Login',
                'description' => 'You logged into your account',
                'time' => Carbon::parse($user->last_login_at)->diffForHumans(),
                'icon' => 'log-in',
                'color' => 'info'
            ]);
        }
        
        return $activities->take(5);
    }

    /**
     * Get weekly schedule
     */
    private function getWeeklySchedule()
    {
        return [
            'Monday' => ['code' => 'ICT 101', 'name' => 'Information Technology', 'time' => '08:00 - 10:00', 'venue' => 'Lab 1'],
            'Tuesday' => ['code' => 'ICT 102', 'name' => 'Computer Applications', 'time' => '10:00 - 12:00', 'venue' => 'Lab 2'],
            'Wednesday' => ['code' => 'ICT 103', 'name' => 'Programming Fundamentals', 'time' => '14:00 - 16:00', 'venue' => 'Class A'],
            'Thursday' => ['code' => 'ICT 104', 'name' => 'Database Systems', 'time' => '08:00 - 10:00', 'venue' => 'Lab 1'],
            'Friday' => ['code' => 'ICT 105', 'name' => 'Web Development', 'time' => '10:00 - 12:00', 'venue' => 'Lab 2'],
        ];
    }

    /**
     * Get announcements
     */
    private function getAnnouncements()
    {
        return collect([
            (object)[
                'title' => 'Library Hours Extended',
                'message' => 'Library will remain open until 10 PM during exam week.',
                'date' => Carbon::now()->format('M d, Y'),
                'priority' => 'high'
            ],
            (object)[
                'title' => 'Guest Lecture',
                'message' => 'Prof. John from MIT will give a guest lecture on Friday.',
                'date' => Carbon::now()->addDays(2)->format('M d, Y'),
                'priority' => 'medium'
            ],
            (object)[
                'title' => 'Scholarship Opportunity',
                'message' => 'Applications open for academic excellence scholarship.',
                'date' => Carbon::now()->format('M d, Y'),
                'priority' => 'low'
            ],
        ]);
    }

    /**
     * Student Dashboard - Main Dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $student = Student::with(['programme', 'academicYear', 'programme.department'])
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        $currentAcademicYear = $this->getCurrentAcademicYear();
        $lastLogin = $this->getLastLogin();
        
        // Get all dashboard data
        $academicData = $this->getAcademicProgress($student);
        $financialData = $this->getFinancialSummary($student);
        $recentActivities = $this->getRecentActivities($student);
        $weeklySchedule = $this->getWeeklySchedule();
        $announcements = $this->getAnnouncements();
        
        return view('student.dashboard', compact(
            'user',
            'student',
            'currentAcademicYear',
            'lastLogin',
            'academicData',
            'financialData',
            'recentActivities',
            'weeklySchedule',
            'announcements'
        ));
    }

    /**
     * Overview page
     */
    public function overview()
    {
        $user = Auth::user();
        $student = $user->student;
        
        if (!$student) {
            abort(404, 'Student record not found');
        }
        
        $currentAcademicYear = $this->getCurrentAcademicYear();
        $lastLogin = $this->getLastLogin();
        $academicData = $this->getAcademicProgress($student);
        $financialData = $this->getFinancialSummary($student);
        
        return view('student.overview', compact(
            'user', 
            'student',
            'currentAcademicYear',
            'lastLogin',
            'academicData',
            'financialData'
        ));
    }

    /**
     * Notifications page
     */
    public function notifications()
    {
        $user = Auth::user();
        $student = $user->student;
        
        if (!$student) {
            abort(404, 'Student record not found');
        }
        
        $currentAcademicYear = $this->getCurrentAcademicYear();
        $lastLogin = $this->getLastLogin();
        
        $notifications = collect([
            (object) [
                'id' => 1,
                'title' => 'Fee Payment Reminder',
                'message' => 'Your tuition fee payment is due soon.',
                'type' => 'warning',
                'is_read' => false,
                'created_at' => now()->subDays(2)
            ],
            (object) [
                'id' => 2,
                'title' => 'Registration Confirmed',
                'message' => 'Your registration has been confirmed successfully.',
                'type' => 'success',
                'is_read' => true,
                'created_at' => now()->subDays(5)
            ],
            (object) [
                'id' => 3,
                'title' => 'Examination Schedule',
                'message' => 'End of semester examination schedule has been published.',
                'type' => 'info',
                'is_read' => false,
                'created_at' => now()->subDays(10)
            ],
        ]);
        
        return view('student.notifications', compact(
            'user', 
            'student',
            'notifications',
            'currentAcademicYear',
            'lastLogin'
        ));
    }
}