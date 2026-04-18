<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    /**
     * Display admission statistics dashboard
     */
    public function statistics(Request $request)
    {
        // Get filter parameters
        $year = $request->get('year', date('Y'));
        $intake = $request->get('intake', 'March');
        
        // ===========================================
        // APPLICATION STATISTICS
        // ===========================================
        
        // Total applications by status
        $applicationsByStatus = DB::table('applications')
            ->select('status', DB::raw('count(*) as total'))
            ->whereYear('created_at', $year)
            ->groupBy('status')
            ->get()
            ->keyBy('status');
        
        // Applications by intake
        $applicationsByIntake = DB::table('applications')
            ->select('intake', DB::raw('count(*) as total'))
            ->whereYear('created_at', $year)
            ->groupBy('intake')
            ->get();
        
        // Monthly applications trend
        $monthlyApplications = DB::table('applications')
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as total'))
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->orderBy(DB::raw('MONTH(created_at)'))
            ->get();
        
        // Applications by programme
        $applicationsByProgramme = DB::table('applications as a')
            ->select('prog.name as programme', DB::raw('count(*) as total'))
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereYear('a.created_at', $year)
            ->groupBy('prog.name')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(10)
            ->get();
        
        // ===========================================
        // SELECTION STATISTICS
        // ===========================================
        
        // Selection rate
        $submittedCount = DB::table('applications')
            ->where('status', 'submitted')
            ->whereYear('created_at', $year)
            ->count();
        
        $approvedCount = DB::table('applications')
            ->whereIn('status', ['approved', 'registered'])
            ->whereYear('created_at', $year)
            ->count();
        
        $selectionRate = $submittedCount > 0 ? round(($approvedCount / $submittedCount) * 100, 2) : 0;
        
        // Selection by programme
        $selectionByProgramme = DB::table('applications as a')
            ->select(
                'prog.name as programme',
                DB::raw("SUM(CASE WHEN a.status = 'submitted' THEN 1 ELSE 0 END) as submitted"),
                DB::raw("SUM(CASE WHEN a.status IN ('approved', 'registered') THEN 1 ELSE 0 END) as selected"),
                DB::raw("ROUND((SUM(CASE WHEN a.status IN ('approved', 'registered') THEN 1 ELSE 0 END) / NULLIF(SUM(CASE WHEN a.status = 'submitted' THEN 1 ELSE 0 END), 0)) * 100, 2) as selection_rate")
            )
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereYear('a.created_at', $year)
            ->groupBy('prog.name')
            ->orderByDesc('submitted')
            ->get();
        
        // ===========================================
        // DEMOGRAPHIC STATISTICS
        // ===========================================
        
        // Gender distribution
        $genderDistribution = DB::table('application_personal_infos as p')
            ->select('p.gender', DB::raw('count(*) as total'))
            ->join('applications as a', 'p.application_id', '=', 'a.id')
            ->whereYear('a.created_at', $year)
            ->groupBy('p.gender')
            ->get();
        
        // Age distribution
        $ageGroups = DB::table('application_personal_infos as p')
            ->select(DB::raw("
                CASE 
                    WHEN TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) < 18 THEN 'Under 18'
                    WHEN TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) BETWEEN 18 AND 20 THEN '18-20'
                    WHEN TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) BETWEEN 21 AND 25 THEN '21-25'
                    WHEN TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) BETWEEN 26 AND 30 THEN '26-30'
                    ELSE '30+'
                END as age_group
            "), DB::raw('count(*) as total'))
            ->join('applications as a', 'p.application_id', '=', 'a.id')
            ->whereYear('a.created_at', $year)
            ->groupBy('age_group')
            ->get();
        
        // Region distribution
        $regionDistribution = DB::table('application_contacts as c')
            ->select('c.region', DB::raw('count(*) as total'))
            ->join('applications as a', 'c.application_id', '=', 'a.id')
            ->whereYear('a.created_at', $year)
            ->groupBy('c.region')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(10)
            ->get();
        
        // ===========================================
        // ACADEMIC STATISTICS
        // ===========================================
        
        // CSEE Points distribution
        $cseePointsDistribution = DB::table('application_academics as ac')
            ->select(DB::raw("
                CASE 
                    WHEN csee_points BETWEEN 7 AND 15 THEN '7-15 (Excellent)'
                    WHEN csee_points BETWEEN 16 AND 20 THEN '16-20 (Very Good)'
                    WHEN csee_points BETWEEN 21 AND 25 THEN '21-25 (Good)'
                    WHEN csee_points BETWEEN 26 AND 30 THEN '26-30 (Average)'
                    ELSE '31-36 (Below Average)'
                END as points_range
            "), DB::raw('count(*) as total'))
            ->join('applications as a', 'ac.application_id', '=', 'a.id')
            ->whereYear('a.created_at', $year)
            ->whereNotNull('ac.csee_points')
            ->groupBy('points_range')
            ->get();
        
        // Division distribution
        $divisionDistribution = DB::table('application_academics as ac')
            ->select('ac.csee_division', DB::raw('count(*) as total'))
            ->join('applications as a', 'ac.application_id', '=', 'a.id')
            ->whereYear('a.created_at', $year)
            ->whereNotNull('ac.csee_division')
            ->groupBy('ac.csee_division')
            ->get();
        
        // Students with ACSEE
        $acseeStats = [
            'with_acsee' => DB::table('application_academics as ac')
                ->join('applications as a', 'ac.application_id', '=', 'a.id')
                ->whereYear('a.created_at', $year)
                ->whereNotNull('ac.acsee_principal_passes')
                ->count(),
            'avg_passes' => DB::table('application_academics as ac')
                ->join('applications as a', 'ac.application_id', '=', 'a.id')
                ->whereYear('a.created_at', $year)
                ->whereNotNull('ac.acsee_principal_passes')
                ->avg('ac.acsee_principal_passes'),
        ];
        
        // ===========================================
        // PERFORMANCE METRICS
        // ===========================================
        
        // Average processing time (from submission to approval)
        $avgProcessingTime = DB::table('applications')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(DAY, submitted_at, approved_at)) as avg_days'))
            ->whereNotNull('submitted_at')
            ->whereNotNull('approved_at')
            ->whereYear('created_at', $year)
            ->first();
        
        // Conversion rate (from application to student)
        $conversionRate = DB::table('applications as a')
            ->select(DB::raw("
                ROUND(
                    (COUNT(DISTINCT s.id) / NULLIF(COUNT(DISTINCT a.id), 0)) * 100, 2
                ) as conversion_rate
            "))
            ->leftJoin('students as s', 'a.id', '=', 's.application_id')
            ->whereYear('a.created_at', $year)
            ->first();
        
        // ===========================================
        // COMPARISON WITH PREVIOUS YEAR
        // ===========================================
        
        $previousYear = $year - 1;
        
        $yearlyComparison = [
            'current_year_applications' => DB::table('applications')->whereYear('created_at', $year)->count(),
            'previous_year_applications' => DB::table('applications')->whereYear('created_at', $previousYear)->count(),
            'current_year_approved' => DB::table('applications')->whereIn('status', ['approved', 'registered'])->whereYear('created_at', $year)->count(),
            'previous_year_approved' => DB::table('applications')->whereIn('status', ['approved', 'registered'])->whereYear('created_at', $previousYear)->count(),
        ];
        
        $yearlyComparison['applications_growth'] = $yearlyComparison['previous_year_applications'] > 0 
            ? round((($yearlyComparison['current_year_applications'] - $yearlyComparison['previous_year_applications']) / $yearlyComparison['previous_year_applications']) * 100, 2)
            : 0;
        
        // ===========================================
        // AVAILABLE YEARS FOR FILTER
        // ===========================================
        
        $availableYears = DB::table('applications')
            ->select(DB::raw('DISTINCT YEAR(created_at) as year'))
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();
        
        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }
        
        return view('admission.reports.statistics', compact(
            'year', 'intake',
            'applicationsByStatus', 'applicationsByIntake', 'monthlyApplications', 'applicationsByProgramme',
            'submittedCount', 'approvedCount', 'selectionRate', 'selectionByProgramme',
            'genderDistribution', 'ageGroups', 'regionDistribution',
            'cseePointsDistribution', 'divisionDistribution', 'acseeStats',
            'avgProcessingTime', 'conversionRate', 'yearlyComparison', 'availableYears'
        ));
    }
    
    /**
     * Display program statistics
     */
    public function programStatistics(Request $request)
    {
        // Get filters
        $year = $request->get('year', date('Y'));
        $programmeId = $request->get('programme_id');
        $intake = $request->get('intake', 'March');
        
        // Get all programmes for filter
        $programmes = DB::table('programmes')
            ->where('is_active', 1)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        // Get selected programme details
        $selectedProgramme = null;
        if ($programmeId) {
            $selectedProgramme = DB::table('programmes')->where('id', $programmeId)->first();
        }
        
        // ===========================================
        // PROGRAMME APPLICATIONS STATISTICS
        // ===========================================
        
        $query = DB::table('applications as a')
            ->select(
                'a.status',
                DB::raw('count(*) as total')
            )
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereYear('a.created_at', $year)
            ->where('a.intake', $intake);
        
        if ($programmeId) {
            $query->where('a.selected_program_id', $programmeId);
        }
        
        $programmeStatusCounts = $query->groupBy('a.status')->get()->keyBy('status');
        
        // ===========================================
        // PROGRAMME APPLICANT DETAILS
        // ===========================================
        
        $applicantsQuery = DB::table('applications as a')
            ->select(
                'a.id',
                'a.application_number',
                'a.status',
                'a.submitted_at',
                'a.approved_at',
                'p.first_name',
                'p.middle_name',
                'p.last_name',
                'p.gender',
                'p.date_of_birth',
                'u.email',
                'u.phone',
                'ac.csee_points',
                'ac.csee_division',
                'ac.acsee_principal_passes'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->leftJoin('application_academics as ac', 'a.id', '=', 'ac.application_id')
            ->whereYear('a.created_at', $year)
            ->where('a.intake', $intake);
        
        if ($programmeId) {
            $applicantsQuery->where('a.selected_program_id', $programmeId);
        }
        
        $applicants = $applicantsQuery->orderByDesc('a.submitted_at')->paginate(20)->withQueryString();
        
        // ===========================================
        // PROGRAMME STATISTICS
        // ===========================================
        
        // Top programmes by applications
        $topProgrammes = DB::table('applications as a')
            ->select('prog.name', 'prog.code', DB::raw('count(*) as total_applications'))
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereYear('a.created_at', $year)
            ->where('a.intake', $intake)
            ->groupBy('prog.name', 'prog.code')
            ->orderByDesc('total_applications')
            ->limit(10)
            ->get();
        
        // Competition ratio (applications per slot)
        $competitionRatio = DB::table('applications as a')
            ->select(
                'prog.name',
                'prog.capacity',
                DB::raw('count(*) as applications'),
                DB::raw('ROUND(count(*) / NULLIF(prog.capacity, 0), 2) as competition_ratio')
            )
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereYear('a.created_at', $year)
            ->where('a.intake', $intake)
            ->groupBy('prog.name', 'prog.capacity')
            ->orderByDesc('competition_ratio')
            ->get();
        
        // Gender distribution by programme
        $genderByProgramme = DB::table('applications as a')
            ->select('prog.name', 'p.gender', DB::raw('count(*) as total'))
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->join('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->whereYear('a.created_at', $year)
            ->where('a.intake', $intake)
            ->groupBy('prog.name', 'p.gender')
            ->get();
        
        // Academic performance by programme
        $academicPerformance = DB::table('applications as a')
            ->select(
                'prog.name',
                DB::raw('ROUND(AVG(ac.csee_points), 2) as avg_csee_points'),
                DB::raw('MIN(ac.csee_points) as min_csee_points'),
                DB::raw('MAX(ac.csee_points) as max_csee_points'),
                DB::raw('ROUND(AVG(ac.acsee_principal_passes), 2) as avg_acsee_passes')
            )
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->join('application_academics as ac', 'a.id', '=', 'ac.application_id')
            ->whereYear('a.created_at', $year)
            ->where('a.intake', $intake)
            ->groupBy('prog.name')
            ->get();
        
        // Selection rate by programme with details
        $selectionDetails = DB::table('applications as a')
            ->select(
                'prog.name',
                'prog.capacity',
                DB::raw("SUM(CASE WHEN a.status = 'submitted' THEN 1 ELSE 0 END) as submitted"),
                DB::raw("SUM(CASE WHEN a.status = 'under_review' THEN 1 ELSE 0 END) as under_review"),
                DB::raw("SUM(CASE WHEN a.status IN ('approved', 'registered') THEN 1 ELSE 0 END) as selected"),
                DB::raw("SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) as rejected"),
                DB::raw("SUM(CASE WHEN a.status = 'waitlisted' THEN 1 ELSE 0 END) as waitlisted")
            )
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereYear('a.created_at', $year)
            ->where('a.intake', $intake)
            ->groupBy('prog.name', 'prog.capacity')
            ->orderByDesc('submitted')
            ->get();
        
        // ===========================================
        // AVAILABLE YEARS FOR FILTER
        // ===========================================
        
        $availableYears = DB::table('applications')
            ->select(DB::raw('DISTINCT YEAR(created_at) as year'))
            ->orderByDesc('year')
            ->pluck('year')
            ->toArray();
        
        if (empty($availableYears)) {
            $availableYears = [date('Y')];
        }
        
        return view('admission.reports.program-statistics', compact(
            'year', 'programmeId', 'intake',
            'programmes', 'selectedProgramme',
            'programmeStatusCounts', 'applicants',
            'topProgrammes', 'competitionRatio', 'genderByProgramme',
            'academicPerformance', 'selectionDetails', 'availableYears'
        ));
    }
    
    /**
     * Export admission statistics to PDF
     */
    public function exportStatisticsPDF(Request $request)
    {
        $year = $request->get('year', date('Y'));
        
        // Get data (reuse from statistics method)
        $applicationsByStatus = DB::table('applications')
            ->select('status', DB::raw('count(*) as total'))
            ->whereYear('created_at', $year)
            ->groupBy('status')
            ->get();
        
        $applicationsByProgramme = DB::table('applications as a')
            ->select('prog.name as programme', DB::raw('count(*) as total'))
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereYear('a.created_at', $year)
            ->groupBy('prog.name')
            ->orderByDesc(DB::raw('count(*)'))
            ->limit(10)
            ->get();
        
        $genderDistribution = DB::table('application_personal_infos as p')
            ->select('p.gender', DB::raw('count(*) as total'))
            ->join('applications as a', 'p.application_id', '=', 'a.id')
            ->whereYear('a.created_at', $year)
            ->groupBy('p.gender')
            ->get();
        
        $pdf = PDF::loadView('admission.reports.statistics-pdf', compact(
            'year', 'applicationsByStatus', 'applicationsByProgramme', 'genderDistribution'
        ));
        
        return $pdf->download("admission_statistics_{$year}.pdf");
    }
    
    /**
     * Export program statistics to PDF
     */
    public function exportProgramStatisticsPDF(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $programmeId = $request->get('programme_id');
        
        $programme = null;
        if ($programmeId) {
            $programme = DB::table('programmes')->where('id', $programmeId)->first();
        }
        
        $selectionDetails = DB::table('applications as a')
            ->select(
                'prog.name',
                'prog.capacity',
                DB::raw("SUM(CASE WHEN a.status = 'submitted' THEN 1 ELSE 0 END) as submitted"),
                DB::raw("SUM(CASE WHEN a.status IN ('approved', 'registered') THEN 1 ELSE 0 END) as selected")
            )
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereYear('a.created_at', $year)
            ->groupBy('prog.name', 'prog.capacity')
            ->get();
        
        $pdf = PDF::loadView('admission.reports.program-statistics-pdf', compact(
            'year', 'programme', 'selectionDetails'
        ));
        
        $filename = $programmeId ? "program_statistics_{$programme->code}_{$year}.pdf" : "program_statistics_{$year}.pdf";
        
        return $pdf->download($filename);
    }
    
    /**
     * Export data to CSV
     */
    public function exportCSV(Request $request, $type)
    {
        $year = $request->get('year', date('Y'));
        
        if ($type === 'applications') {
            $data = DB::table('applications as a')
                ->select(
                    'a.application_number',
                    'a.status',
                    'a.intake',
                    'a.submitted_at',
                    'p.first_name',
                    'p.middle_name',
                    'p.last_name',
                    'p.gender',
                    'u.email',
                    'u.phone',
                    'prog.name as programme'
                )
                ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
                ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
                ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
                ->whereYear('a.created_at', $year)
                ->get();
            
            $filename = "applications_{$year}.csv";
            $headers = ['Application No', 'Status', 'Intake', 'Submitted Date', 'First Name', 'Middle Name', 'Last Name', 'Gender', 'Email', 'Phone', 'Programme'];
        } elseif ($type === 'students') {
            $data = DB::table('students as s')
                ->select(
                    's.registration_number',
                    's.intake',
                    's.study_mode',
                    's.status as student_status',
                    'p.first_name',
                    'p.middle_name',
                    'p.last_name',
                    'u.email',
                    'u.phone',
                    'prog.name as programme'
                )
                ->leftJoin('application_personal_infos as p', 's.application_id', '=', 'p.application_id')
                ->leftJoin('users as u', 's.user_id', '=', 'u.id')
                ->leftJoin('programmes as prog', 's.programme_id', '=', 'prog.id')
                ->whereYear('s.created_at', $year)
                ->get();
            
            $filename = "students_{$year}.csv";
            $headers = ['Reg No', 'Intake', 'Study Mode', 'Status', 'First Name', 'Middle Name', 'Last Name', 'Email', 'Phone', 'Programme'];
        } else {
            return redirect()->back()->with('error', 'Invalid export type');
        }
        
        $callback = function() use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);
            
            foreach ($data as $row) {
                $rowArray = (array) $row;
                fputcsv($file, array_values($rowArray));
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}