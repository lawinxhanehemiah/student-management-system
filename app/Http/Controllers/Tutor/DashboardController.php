<?php

namespace App\Http\Controllers\Tutor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Course;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get assigned courses with student count
        $assignedCourses = Course::where('tutor_id', $user->id)
            ->withCount('students')
            ->get();
        
        $totalCourses = $assignedCourses->count();
        $totalStudents = $assignedCourses->sum('students_count');
        
        // TEMPORARY: Set default values for attendance and results
        $attendanceStats = ['present' => 0, 'absent' => 0, 'late' => 0];
        $pendingAssessments = 0;
        $recentResults = collect([]);
        $weeklyData = ['days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], 'scores' => [0, 0, 0, 0, 0, 0, 0]];
        $coursePerformance = [];
        
        // Check if results table exists and get data
        if (Schema::hasTable('results')) {
            try {
                // Get pending assessments count
                $pendingAssessments = DB::table('results')
                    ->where('tutor_id', $user->id)
                    ->where('status', 'pending')
                    ->count();
                
                // Get recent results
                $recentResults = DB::table('results')
                    ->leftJoin('students', 'results.student_id', '=', 'students.id')
                    ->leftJoin('courses', 'results.course_id', '=', 'courses.id')
                    ->where('results.tutor_id', $user->id)
                    ->select('results.*', 'students.registration_number', 'courses.name as course_name')
                    ->orderBy('results.created_at', 'desc')
                    ->limit(5)
                    ->get();
                
                // Get weekly performance
                $weeklyData = $this->getWeeklyPerformanceFromDB($user->id);
                
                // Get course performance
                $coursePerformance = $this->getCoursePerformanceFromDB($user->id, $assignedCourses);
                
            } catch (\Exception $e) {
                // Log error but continue with default values
                \Log::error('Error fetching results: ' . $e->getMessage());
            }
        }
        
        // Check if attendances table exists
        if (Schema::hasTable('attendances')) {
            try {
                $currentMonth = Carbon::now()->month;
                $currentYear = Carbon::now()->year;
                
                $attendanceStats = [
                    'present' => DB::table('attendances')
                        ->where('tutor_id', $user->id)
                        ->whereMonth('date', $currentMonth)
                        ->whereYear('date', $currentYear)
                        ->where('status', 'present')
                        ->count(),
                    'absent' => DB::table('attendances')
                        ->where('tutor_id', $user->id)
                        ->whereMonth('date', $currentMonth)
                        ->whereYear('date', $currentYear)
                        ->where('status', 'absent')
                        ->count(),
                    'late' => DB::table('attendances')
                        ->where('tutor_id', $user->id)
                        ->whereMonth('date', $currentMonth)
                        ->whereYear('date', $currentYear)
                        ->where('status', 'late')
                        ->count(),
                ];
            } catch (\Exception $e) {
                // Use default values
            }
        }
        
        // If no course performance data, create from assigned courses
        if (empty($coursePerformance)) {
            foreach ($assignedCourses as $course) {
                $coursePerformance[] = [
                    'name' => $course->name,
                    'average' => 0,
                    'students' => $course->students_count,
                    'code' => $course->code ?? '',
                ];
            }
        }
        
        // Recent activities (placeholder)
        $recentActivities = [
            [
                'icon' => 'bell',
                'message' => 'Welcome to your tutor dashboard',
                'time' => 'Just now',
                'type' => 'info'
            ]
        ];
        
        return view('dashboards.tutor', compact(
            'user',
            'assignedCourses',
            'totalCourses',
            'totalStudents',
            'attendanceStats',
            'pendingAssessments',
            'recentResults',
            'weeklyData',
            'coursePerformance',
            'recentActivities'
        ));
    }
    
    private function getWeeklyPerformanceFromDB($tutorId)
    {
        $days = [];
        $scores = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $days[] = $date->format('D');
            
            $avgScore = DB::table('results')
                ->where('tutor_id', $tutorId)
                ->whereDate('created_at', $date)
                ->avg('score');
            
            $scores[] = round($avgScore ?? 0, 1);
        }
        
        return [
            'days' => $days,
            'scores' => $scores,
        ];
    }
    
    private function getCoursePerformanceFromDB($tutorId, $assignedCourses)
    {
        $performance = [];
        
        foreach ($assignedCourses as $course) {
            $avgScore = DB::table('results')
                ->where('tutor_id', $tutorId)
                ->where('course_id', $course->id)
                ->avg('score');
            
            $performance[] = [
                'name' => $course->name,
                'average' => round($avgScore ?? 0, 1),
                'students' => $course->students_count,
                'code' => $course->code ?? '',
            ];
        }
        
        return $performance;
    }
}