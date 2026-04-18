<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdmissionController extends Controller
{
    public function index()
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        // Get current date ranges
        $today = now();
        $startOfMonth = now()->startOfMonth();
        $startOfYear = now()->startOfYear();
        $lastMonth = now()->subMonth();
        $lastYear = now()->subYear();
        
        // 1. Total Applications
        $totalApplications = DB::table('applications')->count();
        
        // 2. Applications this month
        $monthlyApplications = DB::table('applications')
            ->whereBetween('created_at', [$startOfMonth, $today])
            ->count();
        
        // 3. Pending Review
        $pendingReview = DB::table('applications')
            ->where('status', 'pending_review')
            ->orWhere('status', 'submitted')
            ->orWhere('status', 'under_review')
            ->count();
        
        // 4. Approved Applications
        $approvedApplications = DB::table('applications')
            ->where('status', 'approved')
            ->count();
        
        // 5. Rejected Applications
        $rejectedApplications = DB::table('applications')
            ->where('status', 'rejected')
            ->count();
        
        // 6. Waitlisted Applications
        $waitlistedApplications = DB::table('applications')
            ->where('status', 'waitlisted')
            ->count();
        
        // 7. Total Programs
        $totalPrograms = DB::table('programmes')
            ->where('is_active', 1)
            ->count();
        
        // 8. Admission Officers Count (users with Admission_Officer role)
        $admissionOfficers = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'Admission_Officer')
            ->orWhere('roles.name', 'AdmissionOfficer')
            ->count();
        
        // 9. Active Officers (users logged in last 30 days)
        $activeOfficers = DB::table('users')
            ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where(function($query) {
                $query->where('roles.name', 'Admission_Officer')
                      ->orWhere('roles.name', 'AdmissionOfficer');
            })
            ->where('users.last_login_at', '>=', now()->subDays(30))
            ->count();
        
        // 10. Application Fees (if you have payments table)
        $applicationFees = 0;
        $monthlyFees = 0;
        
        // Check if payments table exists
        if (DB::getSchemaBuilder()->hasTable('payments')) {
            $applicationFees = DB::table('payments')
                ->where('payment_type', 'application_fee')
                ->sum('amount');
                
            $monthlyFees = DB::table('payments')
                ->where('payment_type', 'application_fee')
                ->whereBetween('created_at', [$startOfMonth, $today])
                ->sum('amount');
        }
        
        // 11. Average Processing Time (in days)
        $avgProcessingTime = DB::table('applications')
            ->whereNotNull('approved_at')
            ->whereNotNull('submitted_at')
            ->selectRaw('AVG(DATEDIFF(approved_at, submitted_at)) as avg_days')
            ->value('avg_days') ?? 0;
        
        // 12. Application Status Percentages
        $totalForPercent = $approvedApplications + $rejectedApplications + $pendingReview + $waitlistedApplications;
        
        if ($totalForPercent > 0) {
            $statusPendingPercent = round(($pendingReview / $totalForPercent) * 100, 1);
            $statusApprovedPercent = round(($approvedApplications / $totalForPercent) * 100, 1);
            $statusRejectedPercent = round(($rejectedApplications / $totalForPercent) * 100, 1);
            $statusWaitlistedPercent = round(($waitlistedApplications / $totalForPercent) * 100, 1);
        } else {
            $statusPendingPercent = 0;
            $statusApprovedPercent = 0;
            $statusRejectedPercent = 0;
            $statusWaitlistedPercent = 0;
        }
        
        // 13. Monthly Application Trends (Last 6 months)
        $monthlyTrends = [];
        $applicationMonths = [];
        $applicationData = [];
        $processedData = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = now()->subMonths($i)->startOfMonth();
            $monthEnd = now()->subMonths($i)->endOfMonth();
            $monthName = $monthStart->format('M');
            
            // Total applications in this month
            $monthlyTotal = DB::table('applications')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();
            
            // Processed applications in this month
            $monthlyProcessed = DB::table('applications')
                ->whereIn('status', ['approved', 'rejected', 'waitlisted'])
                ->whereBetween('updated_at', [$monthStart, $monthEnd])
                ->count();
            
            $applicationMonths[] = $monthName;
            $applicationData[] = $monthlyTotal;
            $processedData[] = $monthlyProcessed;
        }
        
        // 14. Program-wise Application Distribution
        $programDistribution = DB::table('applications')
            ->select(
                'programmes.name as program_name',
                DB::raw('COUNT(applications.id) as total_applications')
            )
            ->leftJoin('programmes', 'applications.selected_program_id', '=', 'programmes.id')
            ->whereNotNull('applications.selected_program_id')
            ->groupBy('programmes.name')
            ->orderBy('total_applications', 'desc')
            ->limit(8)
            ->get();
        
        // 15. Entry Level Distribution
        $entryLevelDistribution = DB::table('applications')
            ->select(
                'entry_level',
                DB::raw('COUNT(*) as total')
            )
            ->whereNotNull('entry_level')
            ->groupBy('entry_level')
            ->orderBy('total', 'desc')
            ->get();
        
        // 16. Recent Applications (last 10)
        $recentApplications = DB::table('applications')
            ->select(
                'applications.*',
                'application_personal_infos.first_name',
                'application_personal_infos.last_name',
                'programmes.name as program_name'
            )
            ->leftJoin('application_personal_infos', 'applications.id', '=', 'application_personal_infos.application_id')
            ->leftJoin('programmes', 'applications.selected_program_id', '=', 'programmes.id')
            ->orderBy('applications.created_at', 'desc')
            ->limit(10)
            ->get();
        
        // 17. Today's Applications
        $todaysApplications = DB::table('applications')
            ->whereDate('created_at', $today)
            ->count();
        
        // 18. Applications Needing Immediate Attention (pending more than 7 days)
        $needsAttention = DB::table('applications')
            ->whereIn('status', ['submitted', 'pending_review', 'under_review'])
            ->where('created_at', '<=', now()->subDays(7))
            ->count();
        
        // 19. Admission Letters Sent This Month
        $lettersSentThisMonth = DB::table('applications')
            ->where('status', 'approved')
            ->whereNotNull('admission_letter_sent_at')
            ->whereBetween('admission_letter_sent_at', [$startOfMonth, $today])
            ->count();
        
        // 20. Pending Admission Letters
        $pendingLetters = DB::table('applications')
            ->where('status', 'approved')
            ->whereNull('admission_letter_sent_at')
            ->count();
        
        // Prepare data for view
        $data = [
            // Basic counts
            'totalApplications' => $totalApplications,
            'monthlyApplications' => $monthlyApplications,
            'pendingReview' => $pendingReview,
            'approvedApplications' => $approvedApplications,
            'rejectedApplications' => $rejectedApplications,
            'waitlistedApplications' => $waitlistedApplications,
            'todaysApplications' => $todaysApplications,
            'needsAttention' => $needsAttention,
            'lettersSentThisMonth' => $lettersSentThisMonth,
            'pendingLetters' => $pendingLetters,
            
            // Financial (if available)
            'applicationFees' => $applicationFees,
            'monthlyFees' => $monthlyFees,
            
            // Programs and Staff
            'totalPrograms' => $totalPrograms,
            'admissionOfficers' => $admissionOfficers,
            'activeOfficers' => $activeOfficers,
            
            // Processing
            'avgProcessingTime' => round($avgProcessingTime, 1),
            
            // Percentages
            'statusPendingPercent' => $statusPendingPercent,
            'statusApprovedPercent' => $statusApprovedPercent,
            'statusRejectedPercent' => $statusRejectedPercent,
            'statusWaitlistedPercent' => $statusWaitlistedPercent,
            
            // Charts data
            'applicationMonths' => $applicationMonths,
            'applicationData' => $applicationData,
            'processedData' => $processedData,
            
            // Additional data
            'programDistribution' => $programDistribution,
            'entryLevelDistribution' => $entryLevelDistribution,
            'recentApplications' => $recentApplications,
            
            // Current date info
            'currentMonth' => $today->format('F Y'),
            'currentDate' => $today->format('l, F j, Y'),
        ];
        
        return view('dashboards.admission', $data);
    }
    
    /**
     * Show admission statistics
     */
    public function statistics()
    {
        // You can add more detailed statistics here
        return $this->index();
    }
    
    /**
     * Show admission reports
     */
    public function reports()
    {
        // Generate reports view
        $data = [
            'totalApplications' => DB::table('applications')->count(),
            'approvedCount' => DB::table('applications')->where('status', 'approved')->count(),
            'rejectedCount' => DB::table('applications')->where('status', 'rejected')->count(),
            'waitlistedCount' => DB::table('applications')->where('status', 'waitlisted')->count(),
        ];
        
        return view('admission.reports', $data);
    }
    
    /**
     * Manage programs
     */
    public function programs()
    {
        $programs = DB::table('programmes')
            ->orderBy('name')
            ->get();
        
        return view('admission.programs', compact('programs'));
    }
    
    /**
     * Admission settings
     */
    public function settings()
    {
        return view('admission.settings');
    }

    
}