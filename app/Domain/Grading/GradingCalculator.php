<?php
// app/Domain/Grading/GradingCalculator.php - Pure math, no config

namespace App\Domain\Grading;

class GradingCalculator
{
    public function calculateWeightedScore(float $cw, float $exam, float $cwWeight, float $examWeight): float
    {
        return round(($cw * ($cwWeight / 100)) + ($exam * ($examWeight / 100)), 2);
    }
    
    public function getGradeFromScore(float $score, array $gradingBands): array
    {
        foreach ($gradingBands as $band) {
            if ($score >= $band['min_score'] && $score <= $band['max_score']) {
                return [
                    'grade' => $band['grade'],
                    'grade_point' => $band['grade_point']
                ];
            }
        }
        
        throw new \Exception("Score {$score} outside grading bands");
    }
    
    public function getResultStatus(float $score, float $passMark): string
    {
        return $score >= $passMark ? 'pass' : 'fail';
    }
}