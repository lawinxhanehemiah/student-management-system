<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Programme;

class CoursesTableSeeder extends Seeder
{
    public function run()
    {
        // Get programmes
        $pst = Programme::where('code', 'PST')->first();
        $cmt = Programme::where('code', 'CMT')->first();
        $bat = Programme::where('code', 'BAT')->first();
        $swt = Programme::where('code', 'SWT')->first();
        $cdt = Programme::where('code', 'CDT')->first();
        
        // Sample courses for Pharmaceutical Sciences (PST)
        if ($pst) {
            $courses = [
                // Year 1, Semester 1
                ['code' => 'PST101', 'name' => 'Introduction to Pharmacy', 'level' => 1, 'semester' => 1, 'credit_hours' => 3],
                ['code' => 'PST102', 'name' => 'Pharmaceutical Chemistry I', 'level' => 1, 'semester' => 1, 'credit_hours' => 4],
                ['code' => 'PST103', 'name' => 'Anatomy and Physiology', 'level' => 1, 'semester' => 1, 'credit_hours' => 4],
                ['code' => 'PST104', 'name' => 'Biochemistry', 'level' => 1, 'semester' => 1, 'credit_hours' => 3],
                
                // Year 1, Semester 2
                ['code' => 'PST105', 'name' => 'Pharmaceutical Chemistry II', 'level' => 1, 'semester' => 2, 'credit_hours' => 4],
                ['code' => 'PST106', 'name' => 'Pharmacology I', 'level' => 1, 'semester' => 2, 'credit_hours' => 3],
                ['code' => 'PST107', 'name' => 'Pharmaceutics I', 'level' => 1, 'semester' => 2, 'credit_hours' => 4],
                ['code' => 'PST108', 'name' => 'Microbiology', 'level' => 1, 'semester' => 2, 'credit_hours' => 3],
                
                // Year 2, Semester 1
                ['code' => 'PST201', 'name' => 'Pharmacology II', 'level' => 2, 'semester' => 1, 'credit_hours' => 4],
                ['code' => 'PST202', 'name' => 'Pharmaceutics II', 'level' => 2, 'semester' => 1, 'credit_hours' => 4],
                ['code' => 'PST203', 'name' => 'Pharmacognosy', 'level' => 2, 'semester' => 1, 'credit_hours' => 3],
                ['code' => 'PST204', 'name' => 'Clinical Pharmacy I', 'level' => 2, 'semester' => 1, 'credit_hours' => 3],
            ];
            
            foreach ($courses as $course) {
                Course::updateOrCreate(
                    ['code' => $course['code']],
                    array_merge($course, ['programme_id' => $pst->id, 'status' => 'active'])
                );
            }
        }
        
        // Sample courses for Clinical Medicine (CMT)
        if ($cmt) {
            $courses = [
                // Year 1, Semester 1
                ['code' => 'CMT101', 'name' => 'Human Anatomy I', 'level' => 1, 'semester' => 1, 'credit_hours' => 4],
                ['code' => 'CMT102', 'name' => 'Physiology I', 'level' => 1, 'semester' => 1, 'credit_hours' => 4],
                ['code' => 'CMT103', 'name' => 'Biochemistry', 'level' => 1, 'semester' => 1, 'credit_hours' => 3],
                ['code' => 'CMT104', 'name' => 'Introduction to Clinical Medicine', 'level' => 1, 'semester' => 1, 'credit_hours' => 2],
                
                // Year 1, Semester 2
                ['code' => 'CMT105', 'name' => 'Human Anatomy II', 'level' => 1, 'semester' => 2, 'credit_hours' => 4],
                ['code' => 'CMT106', 'name' => 'Physiology II', 'level' => 1, 'semester' => 2, 'credit_hours' => 4],
                ['code' => 'CMT107', 'name' => 'Pathology', 'level' => 1, 'semester' => 2, 'credit_hours' => 3],
                ['code' => 'CMT108', 'name' => 'Pharmacology', 'level' => 1, 'semester' => 2, 'credit_hours' => 3],
            ];
            
            foreach ($courses as $course) {
                Course::updateOrCreate(
                    ['code' => $course['code']],
                    array_merge($course, ['programme_id' => $cmt->id, 'status' => 'active'])
                );
            }
        }
        
        // Sample courses for Business Administration (BAT)
        if ($bat) {
            $courses = [
                ['code' => 'BAT101', 'name' => 'Principles of Management', 'level' => 1, 'semester' => 1, 'credit_hours' => 3],
                ['code' => 'BAT102', 'name' => 'Business Mathematics', 'level' => 1, 'semester' => 1, 'credit_hours' => 3],
                ['code' => 'BAT103', 'name' => 'Introduction to Accounting', 'level' => 1, 'semester' => 1, 'credit_hours' => 3],
                ['code' => 'BAT104', 'name' => 'Business Communication', 'level' => 1, 'semester' => 1, 'credit_hours' => 2],
            ];
            
            foreach ($courses as $course) {
                Course::updateOrCreate(
                    ['code' => $course['code']],
                    array_merge($course, ['programme_id' => $bat->id, 'status' => 'active'])
                );
            }
        }
    }
}