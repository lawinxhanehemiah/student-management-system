<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\Module;
use App\Services\ResultService;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MinistryResultsImport implements ToArray, WithHeadingRow
{
    protected $academicYearId;
    protected $semester;
    protected $batchId;
    protected $resultService;

    public function __construct($academicYearId, $semester, $batchId, ResultService $resultService)
    {
        $this->academicYearId = $academicYearId;
        $this->semester = $semester;
        $this->batchId = $batchId;
        $this->resultService = $resultService;
    }

    public function array(array $rows)
    {
        foreach ($rows as $row) {
            // Expect columns: nacte_reg_number, module_code, ca_score, exam_score
            $student = Student::where('nacte_reg_number', $row['nacte_reg_number'])->first();
            if (!$student) continue;

            $module = Module::where('code', $row['module_code'])->first();
            if (!$module) continue;

            $externalRef = $row['nacte_reg_number'] . '-' . $row['module_code'] . '-' . $this->academicYearId . '-' . $this->semester;

            $data = [
                'student_id' => $student->id,
                'module_id' => $module->id,
                'academic_year_id' => $this->academicYearId,
                'semester' => $this->semester,
                'ca_score' => $row['ca_score'] ?? null,
                'exam_score' => $row['exam_score'] ?? null,
                'status' => 'published', // ministry results are directly published
                'source' => 'ministry',
                'external_reference' => $externalRef,
                'import_batch_id' => $this->batchId,
            ];

            try {
                $this->resultService->createResult($data);
            } catch (\Exception $e) {
                \Log::error('Ministry import failed: ' . $e->getMessage(), ['row' => $row]);
            }
        }
    }
}