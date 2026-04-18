<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EligibilityService
{
    /**
     * Grade order for comparison
     */
    private $gradeOrder = [
        'A' => 1, 'A-' => 1,
        'B+' => 2, 'B' => 2, 'B-' => 2,
        'C+' => 3, 'C' => 3, 'C-' => 3,
        'D' => 4,
        'E' => 5,
        'F' => 6,
        'S' => 7,
    ];

    /**
     * Division order
     */
    private $divisionOrder = [
        'I' => 1,
        'II' => 2,
        'III' => 3,
        'IV' => 4,
    ];

    /**
     * Get eligible programmes for an applicant based on their results
     */
    public function getEligibleProgrammes($applicationId, $academicData = null)
    {
        try {
            // Get application data if not provided
            if (!$academicData) {
                $academicData = $this->getAcademicData($applicationId);
            }

            if (!$academicData) {
                Log::warning('No academic data found for application: ' . $applicationId);
                return collect();
            }

            // Get all active programmes
            $programmes = DB::table('programmes')
                ->where('is_active', 1)
                ->get();

            $eligibleProgrammes = [];

            foreach ($programmes as $programme) {
                $rule = $this->getEligibilityRule($programme->id);
                
                if (!$rule) {
                    continue;
                }

                $result = $this->checkEligibilityForProgramme($academicData, $rule, $programme);
                
                if ($result['is_eligible']) {
                    $eligibleProgrammes[] = [
                        'programme_id' => $programme->id,
                        'programme_name' => $programme->name,
                        'programme_code' => $programme->code,
                        'entry_level' => $rule->entry_level ?? 'CSEE',
                        'priority' => $this->calculatePriority($academicData, $rule),
                    ];
                }
            }

            usort($eligibleProgrammes, function($a, $b) {
                return $a['priority'] <=> $b['priority'];
            });

            Log::info('Eligible programmes found: ' . count($eligibleProgrammes));
            return collect($eligibleProgrammes);

        } catch (\Exception $e) {
            Log::error('Failed to get eligible programmes: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Check eligibility for a specific programme
     */
    public function checkEligibilityForProgramme($academicData, $rule, $programme = null)
    {
        $failures = [];
        $missingSubjects = [];
        $weakSubjects = [];
        $score = 100;

        Log::info('Checking eligibility for programme: ' . ($programme->name ?? 'Unknown'));

        // 1. CHECK ENTRY LEVEL
        $entryCheck = $this->checkEntryLevel($academicData, $rule);
        if (!$entryCheck['passed']) {
            $failures = array_merge($failures, $entryCheck['failures']);
            $score -= 50;
        }

        // 2. CHECK CSEE DIVISION
        if ($rule->min_csee_division && $academicData->csee_division) {
            $currentDiv = $this->divisionOrder[$academicData->csee_division] ?? 5;
            $requiredDiv = $this->divisionOrder[$rule->min_csee_division] ?? 1;
            
            if ($currentDiv > $requiredDiv) {
                $failures[] = "CSEE Division {$academicData->csee_division} is below required {$rule->min_csee_division}";
                $score -= 25;
            }
        }

        // 3. CHECK CSEE POINTS
        if ($rule->min_csee_points && $academicData->csee_points) {
            if ($rule->points_operator === 'lte') {
                if ($academicData->csee_points > $rule->min_csee_points) {
                    $failures[] = "CSEE points ({$academicData->csee_points}) exceed maximum allowed ({$rule->min_csee_points})";
                    $score -= 20;
                }
            } else if ($rule->points_operator === 'gte') {
                if ($academicData->csee_points < $rule->min_csee_points) {
                    $failures[] = "CSEE points ({$academicData->csee_points}) below minimum required ({$rule->min_csee_points})";
                    $score -= 20;
                }
            }
        }

        // 4. CHECK CORE SUBJECTS (With Normalization)
        $coreSubjects = $this->decodeJson($rule->core_subjects);
        if (!empty($coreSubjects)) {
            $subjectGrades = $this->getSubjectGrades($academicData->application_id);
            $normalizedStudentSubjects = $this->normalizeSubjectArray($subjectGrades);
            
            Log::info('Core subjects required: ' . json_encode($coreSubjects));
            Log::info('Student subjects (normalized): ' . json_encode(array_keys($normalizedStudentSubjects)));
            
            foreach ($coreSubjects as $reqSubject) {
                $normalizedReq = $this->normalizeSubjectName($reqSubject);
                
                // Check if subject exists (using normalized names)
                if (!isset($normalizedStudentSubjects[$normalizedReq])) {
                    $missingSubjects[] = $reqSubject;
                    $score -= 15;
                    Log::info('Missing core subject: ' . $reqSubject . ' (normalized: ' . $normalizedReq . ')');
                    continue;
                }
                
                // CHECK GRADE
                $studentGrade = strtoupper($normalizedStudentSubjects[$normalizedReq]);
                $minGrade = $rule->min_subject_grade ?? 'D';
                
                $studentGradeOrder = $this->gradeOrder[$studentGrade] ?? 99;
                $minGradeOrder = $this->gradeOrder[$minGrade] ?? 4;
                
                if ($studentGradeOrder > $minGradeOrder) {
                    $weakSubjects[] = "{$reqSubject} (Grade: {$studentGrade}, Required: ≥ {$minGrade})";
                    $score -= 10;
                    Log::info('Weak grade for ' . $reqSubject . ': ' . $studentGrade . ' < ' . $minGrade);
                } else {
                    Log::info('Good grade for ' . $reqSubject . ': ' . $studentGrade . ' ≥ ' . $minGrade);
                }
            }
            
            if (!empty($missingSubjects)) {
                $failures[] = "Missing required subjects: " . implode(', ', $missingSubjects);
            }
            
            if (!empty($weakSubjects)) {
                $failures[] = "Weak grades in required subjects: " . implode('; ', $weakSubjects);
            }
        }

        // 5. CHECK ALTERNATIVE SUBJECTS (With Normalization)
        $alternativeSubjects = $this->decodeJson($rule->alternative_subjects);
        if (!empty($alternativeSubjects)) {
            $subjectGrades = $this->getSubjectGrades($academicData->application_id);
            $normalizedStudentSubjects = $this->normalizeSubjectArray($subjectGrades);
            $minCount = $rule->min_alternative_count ?? 1;
            $minGrade = $rule->min_subject_grade ?? 'D';
            
            $foundAlternatives = [];
            $foundGrades = [];
            
            foreach ($alternativeSubjects as $altSubject) {
                $normalizedAlt = $this->normalizeSubjectName($altSubject);
                
                if (isset($normalizedStudentSubjects[$normalizedAlt])) {
                    $studentGrade = strtoupper($normalizedStudentSubjects[$normalizedAlt]);
                    $studentGradeOrder = $this->gradeOrder[$studentGrade] ?? 99;
                    $minGradeOrder = $this->gradeOrder[$minGrade] ?? 4;
                    
                    if ($studentGradeOrder <= $minGradeOrder) {
                        $foundAlternatives[] = $altSubject;
                        $foundGrades[] = "{$altSubject}({$studentGrade})";
                    }
                }
            }
            
            Log::info('Alternative subjects required: ' . $minCount . ' of ' . json_encode($alternativeSubjects));
            Log::info('Found alternatives: ' . json_encode($foundGrades));
            
            if (count($foundAlternatives) < $minCount) {
                $failures[] = "Need at least {$minCount} alternative subject(s) with grade ≥ {$minGrade}. Found: " . (empty($foundGrades) ? 'none' : implode(', ', $foundGrades));
                $score -= 20;
            }
        }

        $isEligible = empty($failures);
        $finalScore = max(0, $score);

        Log::info('Eligibility result: ' . ($isEligible ? 'ELIGIBLE' : 'NOT ELIGIBLE'));
        if (!$isEligible) {
            Log::info('Failures: ' . json_encode($failures));
        }

        return [
            'is_eligible' => $isEligible,
            'score' => $finalScore,
            'failures' => $failures,
            'missing_subjects' => $missingSubjects,
            'weak_subjects' => $weakSubjects,
            'failing_reasons' => implode('; ', $failures),
        ];
    }

    /**
     * Get subject grades for an application with normalized names
     */
    private function getSubjectGrades($applicationId)
    {
        $academic = DB::table('application_academics')
            ->where('application_id', $applicationId)
            ->first();

        if (!$academic) {
            Log::warning('No academic record found for application: ' . $applicationId);
            return [];
        }

        $subjects = DB::table('application_olevel_subjects')
            ->where('application_academic_id', $academic->id)
            ->whereNull('deleted_at')
            ->get();

        $grades = [];
        foreach ($subjects as $subject) {
            $grades[$subject->subject] = $subject->grade;
        }

        return $grades;
    }

    /**
     * Normalize all subject names in an array
     */
    private function normalizeSubjectArray($subjects)
    {
        $normalized = [];
        foreach ($subjects as $name => $grade) {
            $normalized[$this->normalizeSubjectName($name)] = $grade;
        }
        return $normalized;
    }

    /**
     * Normalize subject name for matching
     */
    private function normalizeSubjectName($name)
    {
        $name = strtoupper(trim($name));
        
        // Map subject names to standard format (matching your database)
        $mappings = [
            // Mathematics
            'MATHEMATICS' => 'MATHEMATICS',
            'MATH' => 'MATHEMATICS',
            'BASIC MATHEMATICS' => 'MATHEMATICS',
            'B/MATH' => 'MATHEMATICS',
            'B/MATHS' => 'MATHEMATICS',
            
            // English
            'ENGLISH' => 'ENGLISH',
            'ENGLISH LANGUAGE' => 'ENGLISH',
            'ENGL' => 'ENGLISH',
            
            // Kiswahili
            'KISWAHILI' => 'KISWAHILI',
            'SWAHILI' => 'KISWAHILI',
            'KISW' => 'KISWAHILI',
            
            // Sciences
            'BIOLOGY' => 'BIOLOGY',
            'BIO' => 'BIOLOGY',
            'CHEMISTRY' => 'CHEMISTRY',
            'CHEM' => 'CHEMISTRY',
            'PHYSICS' => 'PHYSICS',
            'PHY' => 'PHYSICS',
            
            // Humanities
            'GEOGRAPHY' => 'GEOGRAPHY',
            'GEO' => 'GEOGRAPHY',
            'HISTORY' => 'HISTORY',
            'HIST' => 'HISTORY',
            'CIVICS' => 'CIVICS',
            'CIV' => 'CIVICS',
            
            // Commercial
            'COMMERCE' => 'COMMERCE',
            'COMM' => 'COMMERCE',
            'BOOK KEEPING' => 'BOOK KEEPING',
            'BOOKKEEPING' => 'BOOK KEEPING',
            'BK' => 'BOOK KEEPING',
            
            // Other
            'LITERATURE' => 'LITERATURE',
            'LIT' => 'LITERATURE',
            'FRENCH' => 'FRENCH',
            'ARABIC' => 'ARABIC',
            'COMPUTER' => 'COMPUTER',
            'ICT' => 'COMPUTER',
        ];
        
        return $mappings[$name] ?? $name;
    }

    /**
     * Decode JSON field safely
     */
    private function decodeJson($value)
    {
        if (empty($value)) {
            return [];
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        
        return [];
    }

    /**
     * Get academic data for an application
     */
    private function getAcademicData($applicationId)
    {
        return DB::table('applications as a')
            ->select('a.id as application_id', 'a.entry_level', 'ac.*')
            ->leftJoin('application_academics as ac', 'a.id', '=', 'ac.application_id')
            ->where('a.id', $applicationId)
            ->first();
    }

    /**
     * Get eligibility rule for a programme
     */
    private function getEligibilityRule($programmeId)
    {
        return DB::table('eligibility_rules')
            ->where('programme_id', $programmeId)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * Check if applicant meets entry level requirements
     */
    private function checkEntryLevel($academicData, $rule)
    {
        $failures = [];
        $requiredLevel = $rule->entry_level ?? 'CSEE';

        switch ($requiredLevel) {
            case 'Degree':
                if (!$academicData->acsee_index_number && !($academicData->diploma_completed ?? false)) {
                    $failures[] = "Degree programmes require ACSEE or Diploma qualification";
                }
                break;
            case 'Diploma':
                if (!$academicData->acsee_index_number && !$academicData->csee_points) {
                    $failures[] = "Diploma programmes require ACSEE or strong CSEE results";
                }
                break;
            case 'ACSEE':
                if (!$academicData->acsee_index_number) {
                    $failures[] = "ACSEE results required for this programme";
                }
                break;
            case 'CSEE':
                if (!$academicData->csee_index_number) {
                    $failures[] = "CSEE results required for this programme";
                }
                break;
        }

        return [
            'passed' => empty($failures),
            'failures' => $failures,
        ];
    }

    /**
     * Calculate priority score for sorting eligible programmes
     */
    private function calculatePriority($academicData, $rule)
    {
        $priority = 0;
        
        if (isset($academicData->entry_level) && $academicData->entry_level === ($rule->entry_level ?? 'CSEE')) {
            $priority -= 10;
        }
        
        if ($rule->min_csee_points && $academicData->csee_points) {
            if ($academicData->csee_points <= $rule->min_csee_points) {
                $priority -= 5;
            }
        }
        
        if ($rule->min_csee_division && $academicData->csee_division) {
            if ($academicData->csee_division === $rule->min_csee_division) {
                $priority -= 3;
            } else if ($this->divisionOrder[$academicData->csee_division] < $this->divisionOrder[$rule->min_csee_division]) {
                $priority -= 2;
            }
        }
        
        return $priority;
    }
}