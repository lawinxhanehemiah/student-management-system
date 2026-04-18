<?php

namespace App\Http\Controllers\Admission;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SelectionController extends Controller
{
    /**
     * Display applicant ranking page
     */
    public function ranking(Request $request)
    {
        // Get programmes for filter
        $programmes = DB::table('programmes')
            ->where('is_active', 1)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        
        // Get filters
        $selectedProgrammeId = $request->get('programme_id');
        $selectedIntake = $request->get('intake', 'March');
        $search = $request->get('search');
        
        // Build query for ranked applicants
        $query = DB::table('applications as a')
            ->select(
                'a.id',
                'a.application_number',
                'a.status',
                'a.intake',
                'a.study_mode',
                'a.selected_program_id',
                'a.submitted_at',
                'a.ranking_score',
                'a.rank_position',
                // From application_personal_infos
                'p.first_name',
                'p.middle_name',
                'p.last_name',
                'p.date_of_birth',
                'p.gender',
                'p.nationality',
                // Phone and email from users table
                'u.phone as phone_number',
                'u.email as email',
                // From application_academics
                'ac.csee_points',
                'ac.csee_division',
                'ac.csee_index_number',
                'ac.acsee_principal_passes',
                'ac.acsee_combination',
                'ac.acsee_year',
                'ac.csee_year',
                // From programmes
                'prog.name as programme_name',
                'prog.code as programme_code',
                'prog.capacity as programme_capacity'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->leftJoin('application_academics as ac', 'a.id', '=', 'ac.application_id')
            ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->where('a.status', 'submitted')
            ->whereNotNull('a.selected_program_id');
        
        // Apply filters
        if ($selectedProgrammeId) {
            $query->where('a.selected_program_id', $selectedProgrammeId);
        }
        
        if ($selectedIntake) {
            $query->where('a.intake', $selectedIntake);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('a.application_number', 'like', "%{$search}%")
                  ->orWhere('p.first_name', 'like', "%{$search}%")
                  ->orWhere('p.last_name', 'like', "%{$search}%")
                  ->orWhere('u.phone', 'like', "%{$search}%")
                  ->orWhere('u.email', 'like', "%{$search}%");
            });
        }
        
        // Get applications
        $applications = $query->get();
        
        // Calculate ranking scores for each applicant if not already calculated
        foreach ($applications as $app) {
            if (!$app->ranking_score) {
                $app->ranking_score = $this->calculateRankingScore($app);
            }
        }
        
        // Sort by ranking score (higher is better)
        $applications = $applications->sortByDesc(function($app) {
            return $app->ranking_score;
        });
        
        // Assign rank positions
        $rank = 1;
        foreach ($applications as $app) {
            $app->rank_position = $rank++;
        }
        
        // Paginate manually
        $perPage = 20;
        $currentPage = $request->get('page', 1);
        $paginatedApps = $applications->forPage($currentPage, $perPage);
        
        // Create paginator
        $applications = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedApps,
            $applications->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // Get statistics
        $statistics = $this->getRankingStatistics($selectedProgrammeId, $selectedIntake);
        
        // Get selection summary by programme
        $selectionSummary = $this->getSelectionSummary($selectedIntake);
        
        return view('admission.selection.ranking', compact(
            'applications',
            'programmes',
            'selectedProgrammeId',
            'selectedIntake',
            'search',
            'statistics',
            'selectionSummary'
        ));
    }
    
   /**
 * Display selected students page
 */
public function selected(Request $request)
{
    // Get programmes for filter
    $programmes = DB::table('programmes')
        ->where('is_active', 1)
        ->where('status', 'active')
        ->orderBy('name')
        ->get();
    
    // Get filters
    $selectedProgrammeId = $request->get('programme_id');
    $selectedIntake = $request->get('intake', 'March');
    $search = $request->get('search');
    
    // Build query for selected students - FIXED: removed user_email
    $query = DB::table('applications as a')
        ->select(
            'a.id',
            'a.application_number',
            'a.status',
            'a.intake',
            'a.study_mode',
            'a.selected_program_id',
            'a.submitted_at',
            'a.admission_letter_sent_at',
            'a.admission_letter_sent_by',
            'a.admission_status',
            'a.admission_date',
            'a.approved_at',
            // From application_personal_infos
            'p.first_name',
            'p.middle_name',
            'p.last_name',
            'p.date_of_birth',
            'p.gender',
            // From users table - use 'email' not 'user_email'
            'u.phone as phone_number',
            'u.email',  // Changed from 'u.email as user_email'
            // From application_academics
            'ac.csee_points',
            'ac.csee_division',
            'ac.acsee_principal_passes',
            // From programmes
            'prog.name as programme_name',
            'prog.code as programme_code'
        )
        ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
        ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
        ->leftJoin('application_academics as ac', 'a.id', '=', 'ac.application_id')
        ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
        ->whereIn('a.status', ['approved', 'registered'])
        ->where('a.intake', $selectedIntake);
    
    // Apply filters
    if ($selectedProgrammeId) {
        $query->where('a.selected_program_id', $selectedProgrammeId);
    }
    
    if ($search) {
        $query->where(function($q) use ($search) {
            $q->where('a.application_number', 'like', "%{$search}%")
              ->orWhere('p.first_name', 'like', "%{$search}%")
              ->orWhere('p.last_name', 'like', "%{$search}%")
              ->orWhere('u.phone', 'like', "%{$search}%")
              ->orWhere('u.email', 'like', "%{$search}%");
        });
    }
    
    $selectedStudents = $query->orderByDesc('a.approved_at')
        ->orderByDesc('a.submitted_at')
        ->paginate(20)
        ->withQueryString();
    
    // Get statistics
    $statistics = [
        'total_selected' => DB::table('applications')
            ->whereIn('status', ['approved', 'registered'])
            ->where('intake', $selectedIntake)
            ->count(),
        'by_programme' => DB::table('applications as a')
            ->select('prog.name', DB::raw('count(*) as count'))
            ->join('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->whereIn('a.status', ['approved', 'registered'])
            ->where('a.intake', $selectedIntake)
            ->groupBy('prog.name')
            ->get(),
        'letters_sent' => DB::table('applications')
            ->whereIn('status', ['approved', 'registered'])
            ->where('intake', $selectedIntake)
            ->whereNotNull('admission_letter_sent_at')
            ->count(),
        'letters_pending' => DB::table('applications')
            ->whereIn('status', ['approved', 'registered'])
            ->where('intake', $selectedIntake)
            ->whereNull('admission_letter_sent_at')
            ->count()
    ];
    
    // Get selection summary by programme
    $selectionSummary = $this->getSelectionSummary($selectedIntake);
    
    return view('admission.selection.selected', compact(
        'selectedStudents',
        'programmes',
        'selectedProgrammeId',
        'selectedIntake',
        'search',
        'statistics',
        'selectionSummary'
    ));
}
    /**
     * Calculate ranking scores (AJAX)
     */
    public function calculateRanking(Request $request)
    {
        try {
            $programmeId = $request->programme_id;
            $intake = $request->intake ?? 'March';
            
            DB::beginTransaction();
            
            // Get all submitted applications
            $query = DB::table('applications as a')
                ->leftJoin('application_academics as ac', 'a.id', '=', 'ac.application_id')
                ->where('a.status', 'submitted')
                ->where('a.intake', $intake);
            
            if ($programmeId) {
                $query->where('a.selected_program_id', $programmeId);
            }
            
            $applications = $query->get(['a.id', 'ac.*']);
            
            $calculated = 0;
            foreach ($applications as $app) {
                // Calculate score
                $score = $this->calculateRankingScoreFromData($app, $app);
                
                // Update application with ranking score
                DB::table('applications')
                    ->where('id', $app->id)
                    ->update([
                        'ranking_score' => $score,
                        'updated_at' => now()
                    ]);
                
                $calculated++;
            }
            
            // Now assign rank positions
            $rankedApps = DB::table('applications')
                ->where('status', 'submitted')
                ->where('intake', $intake)
                ->when($programmeId, function($q) use ($programmeId) {
                    return $q->where('selected_program_id', $programmeId);
                })
                ->orderByDesc('ranking_score')
                ->get();
            
            $position = 1;
            foreach ($rankedApps as $app) {
                DB::table('applications')
                    ->where('id', $app->id)
                    ->update(['rank_position' => $position++]);
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Ranking calculated for {$calculated} applicants.",
                'calculated' => $calculated
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Ranking calculation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to calculate ranking: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Select an applicant (move from submitted to approved)
     */
    public function selectApplicant(Request $request, $id)
    {
        try {
            $application = DB::table('applications')
                ->where('id', $id)
                ->first();
            
            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application not found'
                ], 404);
            }
            
            if ($application->status !== 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only submitted applications can be selected. Current status: ' . $application->status
                ], 400);
            }
            
            DB::table('applications')
                ->where('id', $id)
                ->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => auth()->id(),
                    'updated_at' => now()
                ]);
            
            Log::info('Applicant selected', [
                'application_id' => $id,
                'application_number' => $application->application_number,
                'selected_by' => auth()->id()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Applicant approved successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to select applicant: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to select applicant: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Bulk select applicants
     */
    public function bulkSelect(Request $request)
    {
        try {
            $applicationIds = $request->input('application_ids', []);
            
            if (empty($applicationIds)) {
                return redirect()->back()->with('error', 'No applicants selected.');
            }
            
            $selected = 0;
            $failed = 0;
            
            foreach ($applicationIds as $id) {
                try {
                    $application = DB::table('applications')
                        ->where('id', $id)
                        ->where('status', 'submitted')
                        ->first();
                    
                    if ($application) {
                        DB::table('applications')
                            ->where('id', $id)
                            ->update([
                                'status' => 'approved',
                                'approved_at' => now(),
                                'approved_by' => auth()->id(),
                                'updated_at' => now()
                            ]);
                        $selected++;
                    } else {
                        $failed++;
                    }
                } catch (\Exception $e) {
                    $failed++;
                    Log::error('Failed to select applicant: ' . $e->getMessage(), ['id' => $id]);
                }
            }
            
            $message = "{$selected} applicants selected successfully.";
            if ($failed > 0) {
                $message .= " {$failed} failed.";
            }
            
            return redirect()->back()->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('Bulk selection failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Bulk selection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Move applicant to waiting list
     */
    public function moveToWaitlist(Request $request, $id)
    {
        try {
            $application = DB::table('applications')
                ->where('id', $id)
                ->first();
            
            if (!$application) {
                return response()->json([
                    'success' => false,
                    'message' => 'Application not found'
                ], 404);
            }
            
            DB::table('applications')
                ->where('id', $id)
                ->where('status', 'submitted')
                ->update([
                    'status' => 'waitlisted',
                    'waitlisted_at' => now(),
                    'updated_at' => now()
                ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Applicant moved to waiting list'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move to waiting list: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Auto-select top candidates based on ranking
     */
    public function autoSelect(Request $request)
    {
        try {
            $programmeId = $request->programme_id;
            $intake = $request->intake ?? 'March';
            $numberToSelect = $request->number_to_select ?? 50;
            
            DB::beginTransaction();
            
            // Get top ranked applications
            $topApplications = DB::table('applications')
                ->where('status', 'submitted')
                ->where('intake', $intake)
                ->when($programmeId, function($q) use ($programmeId) {
                    return $q->where('selected_program_id', $programmeId);
                })
                ->orderByDesc('ranking_score')
                ->limit($numberToSelect)
                ->get();
            
            $selected = 0;
            foreach ($topApplications as $app) {
                DB::table('applications')
                    ->where('id', $app->id)
                    ->update([
                        'status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => auth()->id(),
                        'updated_at' => now()
                    ]);
                $selected++;
            }
            
            DB::commit();
            
            return redirect()->back()->with('success', "{$selected} top candidates auto-selected successfully.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Auto selection failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Auto selection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Export ranking to CSV
     */
    public function exportRanking(Request $request)
    {
        $programmeId = $request->programme_id;
        $intake = $request->intake ?? 'March';
        
        $query = DB::table('applications as a')
            ->select(
                'a.application_number',
                'a.ranking_score',
                'a.rank_position',
                'p.first_name',
                'p.middle_name',
                'p.last_name',
                'u.phone as phone_number',
                'u.email as email',
                'ac.csee_points',
                'ac.csee_division',
                'ac.acsee_principal_passes',
                'prog.name as programme_name'
            )
            ->leftJoin('application_personal_infos as p', 'a.id', '=', 'p.application_id')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->leftJoin('application_academics as ac', 'a.id', '=', 'ac.application_id')
            ->leftJoin('programmes as prog', 'a.selected_program_id', '=', 'prog.id')
            ->where('a.status', 'submitted')
            ->where('a.intake', $intake)
            ->orderByDesc('a.ranking_score');
        
        if ($programmeId) {
            $query->where('a.selected_program_id', $programmeId);
        }
        
        $applications = $query->get();
        
        $filename = 'ranking_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];
        
        $callback = function() use ($applications) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'Rank',
                'Application Number',
                'Full Name',
                'Phone',
                'Email',
                'Programme',
                'CSEE Points',
                'CSEE Division',
                'ACSEE Passes',
                'Ranking Score'
            ]);
            
            foreach ($applications as $app) {
                $fullName = trim($app->first_name . ' ' . $app->middle_name . ' ' . $app->last_name);
                fputcsv($file, [
                    $app->rank_position ?? 'N/A',
                    $app->application_number,
                    $fullName,
                    $app->phone_number ?? 'N/A',
                    $app->email ?? 'N/A',
                    $app->programme_name ?? 'N/A',
                    $app->csee_points ?? 'N/A',
                    $app->csee_division ?? 'N/A',
                    $app->acsee_principal_passes ?? 'N/A',
                    $app->ranking_score ?? 'N/A'
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
    
    /**
     * Calculate ranking score for an applicant
     */
    private function calculateRankingScore($application)
    {
        $score = 0;
        
        // CSEE points (lower is better, so convert to higher score)
        // 7 points = 100%, 36 points = 0%
        if ($application->csee_points) {
            $cseeScore = max(0, 100 - (($application->csee_points - 7) / 29 * 100));
            $score += $cseeScore * 0.6; // 60% weight for CSEE
        }
        
        // ACSEE principal passes (more passes = higher score)
        if ($application->acsee_principal_passes && $application->acsee_principal_passes > 0) {
            // 3 principal passes = 100%, 0 = 0%
            $acseeScore = min(100, ($application->acsee_principal_passes / 3) * 100);
            $score += $acseeScore * 0.3; // 30% weight for ACSEE
        } else {
            // No ACSEE means CSEE only - give base score
            $score += 50 * 0.3;
        }
        
        // Division bonus
        $divisionBonus = [
            'I' => 10,
            'II' => 7,
            'III' => 4,
            'IV' => 0
        ];
        $score += $divisionBonus[$application->csee_division] ?? 0;
        
        return round($score, 2);
    }
    
    /**
     * Calculate ranking score from data
     */
    private function calculateRankingScoreFromData($application, $academic)
    {
        $score = 0;
        
        // CSEE points (lower is better, so convert to higher score)
        if ($academic && $academic->csee_points) {
            $cseeScore = max(0, 100 - (($academic->csee_points - 7) / 29 * 100));
            $score += $cseeScore * 0.6;
        }
        
        // ACSEE principal passes
        if ($academic && $academic->acsee_principal_passes && $academic->acsee_principal_passes > 0) {
            $acseeScore = min(100, ($academic->acsee_principal_passes / 3) * 100);
            $score += $acseeScore * 0.3;
        } else {
            $score += 50 * 0.3;
        }
        
        $divisionBonus = ['I' => 10, 'II' => 7, 'III' => 4, 'IV' => 0];
        $score += $divisionBonus[$academic->csee_division ?? 'IV'] ?? 0;
        
        return round($score, 2);
    }
    
    /**
     * Get ranking statistics
     */
    private function getRankingStatistics($programmeId = null, $intake = 'March')
    {
        $query = DB::table('applications as a')
            ->leftJoin('application_academics as ac', 'a.id', '=', 'ac.application_id')
            ->where('a.status', 'submitted')
            ->where('a.intake', $intake);
        
        if ($programmeId) {
            $query->where('a.selected_program_id', $programmeId);
        }
        
        $applications = $query->get();
        
        // Calculate average CSEE points (only for those with values)
        $cseePointsSum = 0;
        $cseeCount = 0;
        $divisionCounts = ['I' => 0, 'II' => 0, 'III' => 0, 'IV' => 0];
        $withAcsee = 0;
        
        foreach ($applications as $app) {
            if ($app->csee_points) {
                $cseePointsSum += $app->csee_points;
                $cseeCount++;
            }
            
            if ($app->csee_division && isset($divisionCounts[$app->csee_division])) {
                $divisionCounts[$app->csee_division]++;
            }
            
            if ($app->acsee_principal_passes && $app->acsee_principal_passes > 0) {
                $withAcsee++;
            }
        }
        
        $stats = [
            'total_applicants' => $applications->count(),
            'avg_csee_points' => $cseeCount > 0 ? round($cseePointsSum / $cseeCount, 2) : 0,
            'division_counts' => $divisionCounts,
            'with_acsee' => $withAcsee,
        ];
        
        // Calculate score distribution
        $scores = [];
        foreach ($applications as $app) {
            $scores[] = $this->calculateRankingScore($app);
        }
        
        if (count($scores) > 0) {
            $stats['score_min'] = round(min($scores), 2);
            $stats['score_max'] = round(max($scores), 2);
            $stats['score_avg'] = round(array_sum($scores) / count($scores), 2);
        } else {
            $stats['score_min'] = 0;
            $stats['score_max'] = 0;
            $stats['score_avg'] = 0;
        }
        
        return $stats;
    }
    
    /**
     * Get selection summary by programme
     */
    private function getSelectionSummary($intake = 'March')
    {
        $programmes = DB::table('programmes')
            ->where('is_active', 1)
            ->where('status', 'active')
            ->get();
        
        $summary = [];
        
        foreach ($programmes as $programme) {
            // Get submitted applications for this programme
            $submittedCount = DB::table('applications')
                ->where('selected_program_id', $programme->id)
                ->where('status', 'submitted')
                ->where('intake', $intake)
                ->count();
            
            // Get under_review applications
            $underReviewCount = DB::table('applications')
                ->where('selected_program_id', $programme->id)
                ->where('status', 'under_review')
                ->where('intake', $intake)
                ->count();
            
            // Get approved applications (these are selected students)
            $approvedCount = DB::table('applications')
                ->where('selected_program_id', $programme->id)
                ->whereIn('status', ['approved', 'registered'])
                ->where('intake', $intake)
                ->count();
            
            // Get rejected applications
            $rejectedCount = DB::table('applications')
                ->where('selected_program_id', $programme->id)
                ->where('status', 'rejected')
                ->where('intake', $intake)
                ->count();
            
            // Get waitlisted applications
            $waitlistedCount = DB::table('applications')
                ->where('selected_program_id', $programme->id)
                ->where('status', 'waitlisted')
                ->where('intake', $intake)
                ->count();
            
            $summary[] = [
                'programme_id' => $programme->id,
                'programme_name' => $programme->name,
                'programme_code' => $programme->code,
                'capacity' => $programme->capacity ?? 0,
                'submitted' => $submittedCount,
                'under_review' => $underReviewCount,
                'approved' => $approvedCount,
                'rejected' => $rejectedCount,
                'waitlisted' => $waitlistedCount,
                'available_slots' => ($programme->capacity ?? 0) - $approvedCount,
                'selection_rate' => $submittedCount > 0 
                    ? round(($approvedCount / $submittedCount) * 100, 2) 
                    : 0
            ];
        }
        
        return $summary;
    }
}