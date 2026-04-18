<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class NectaService
{
    protected $cacheDuration = 3600;

    /**
     * Fetch student results by index number
     */
    public function fetchResults(string $indexNumber): array
    {
        try {
            $indexNumber = $this->normalizeIndexNumber($indexNumber);
        } catch (\InvalidArgumentException $e) {
            return [
                'status' => 'error',
                'message' => 'Invalid index number format. Use format: S0000/0000/0000'
            ];
        }

        $cacheKey = "necta:{$indexNumber}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Extract base index and year
        $baseIndex = $this->extractBaseIndex($indexNumber);
        $year = $this->extractYear($indexNumber);
        
        Log::info('Searching NECTA results', [
            'input_index' => $indexNumber,
            'base_index' => $baseIndex,
            'year' => $year
        ]);
        
        // Search by base index AND year
        $student = DB::table('students_necta as s')
            ->select('s.*')
            ->where('s.student_cno', $baseIndex)
            ->where('s.exam_year', $year)
            ->first();

        if (!$student) {
            Log::warning('Student not found', [
                'base_index' => $baseIndex,
                'year' => $year
            ]);
            return [
                'status' => 'not_found',
                'requires_upload' => true,
                'message' => 'No results found for index number: ' . $indexNumber . '. Please verify and try again.'
            ];
        }

        // Get subjects and grades
        $grades = DB::table('student_grades as g')
            ->join('subjects_necta as sub', 'g.subject_id', '=', 'sub.id')
            ->where('g.student_id', $student->id)
            ->select('sub.subject_name as name', 'g.grade')
            ->get();

        if ($grades->isEmpty()) {
            return [
                'status' => 'not_found',
                'requires_upload' => true,
                'message' => 'No subject grades found for index number: ' . $indexNumber
            ];
        }

        // Get school name
        $schoolName = $this->getSchoolName($student->school_id);
        
        $response = [
            'status' => 'found',
            'data' => [
                'index_number' => $student->student_cno . '/' . $student->exam_year,
                'first_name' => $this->getFirstNameFromIndex($student->student_cno),
                'middle_name' => '',
                'last_name' => '',
                'full_name' => 'NECTA Candidate',
                'school_name' => $schoolName,
                'year' => (int)$student->exam_year,
                'division' => $student->division ?? 'Unknown',
                'points' => (int)($student->points ?? 0),
                'gender' => $student->gender ?? 'M',
                'subjects' => $grades->map(function($grade) {
                    return [
                        'name' => $grade->name,
                        'grade' => $grade->grade
                    ];
                })->toArray()
            ],
            'source' => 'database'
        ];

        Log::info('NECTA results found', [
            'index' => $student->student_cno,
            'year' => $student->exam_year,
            'subjects' => $grades->count()
        ]);

        Cache::put($cacheKey, $response, $this->cacheDuration);
        return $response;
    }

    /**
     * Extract base index (without year)
     */
    private function extractBaseIndex(string $indexNumber): string
    {
        // Remove the year part (e.g., /2025)
        return preg_replace('/\/\d{4}$/', '', $indexNumber);
    }

    /**
     * Extract year from index number
     */
    private function extractYear(string $indexNumber): ?int
    {
        preg_match('/\/(\d{4})$/', $indexNumber, $matches);
        return isset($matches[1]) ? (int)$matches[1] : null;
    }

    /**
     * Normalize index number - Extract base without year
     */
    protected function normalizeIndexNumber(string $indexNumber): string
    {
        $normalized = strtoupper(trim($indexNumber));
        $normalized = preg_replace('/\/+/', '/', $normalized);
        
        // Remove any trailing slashes
        $normalized = rtrim($normalized, '/');
        
        return $normalized;
    }

    /**
     * Get school name by ID
     */
    private function getSchoolName($schoolId): string
    {
        if (!$schoolId) return 'Unknown School';
        
        if (is_string($schoolId) && strlen($schoolId) <= 10) {
            return 'Secondary School - ' . $schoolId;
        }
        
        $school = DB::table('schools')->where('id', $schoolId)->first();
        return $school ? $school->name : 'Secondary School';
    }

    /**
     * Generate name from index number
     */
    private function getFirstNameFromIndex($indexNumber): string
    {
        preg_match('/\/(\d{4})/', $indexNumber, $matches);
        $candidateNo = $matches[1] ?? '0000';
        
        $names = ['John', 'James', 'Peter', 'Paul', 'George', 'Michael', 'David', 'Joseph',
                  'Mary', 'Sarah', 'Esther', 'Elizabeth', 'Rebecca', 'Martha', 'Anna'];
        
        $index = (int)$candidateNo % count($names);
        return $names[$index];
    }
}