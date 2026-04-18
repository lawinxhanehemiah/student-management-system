<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ProgrammeFee;
use App\Models\Programme;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class FeeStructureController extends Controller
{
    /**
     * Display a listing of fee structures (VIEW ONLY)
     */
    public function index(Request $request)
    {
        $query = ProgrammeFee::with(['programme', 'academicYear']);

        // Apply filters
        if ($request->filled('programme_id')) {
            $query->where('programme_id', $request->programme_id);
        }

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }

        $feeStructures = $query->latest()->paginate(15);

        // Get filter options
        $programmes = Programme::where('is_active', true)->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $levels = [1, 2, 3, 4];

        // Calculate stats
        $activeCount = ProgrammeFee::where('is_active', true)->count();

        return view('finance.fee-structures.index', compact(
            'feeStructures',
            'programmes',
            'academicYears',
            'levels',
            'activeCount'
        ));
    }

    /**
     * Display the specified fee structure (VIEW ONLY)
     */
    public function show($id)
    {
        $feeStructure = ProgrammeFee::with(['programme', 'academicYear'])
            ->findOrFail($id);

        return view('finance.fee-structures.show', compact('feeStructure'));
    }

    /**
     * Get fee structures by programme (API for invoice generation)
     */
    public function getByProgramme($programmeId)
    {
        $feeStructures = ProgrammeFee::with('academicYear')
            ->where('programme_id', $programmeId)
            ->where('is_active', true)
            ->get()
            ->map(function($fee) {
                return [
                    'id' => $fee->id,
                    'academic_year' => $fee->academicYear->name,
                    'level' => $fee->level,
                    'registration_fee' => $fee->registration_fee,
                    'semester_1_fee' => $fee->semester_1_fee,
                    'semester_2_fee' => $fee->semester_2_fee,
                    'total' => $fee->registration_fee + $fee->semester_1_fee + $fee->semester_2_fee
                ];
            });

        return response()->json($feeStructures);
    }

    /**
     * Get fee by level (API for invoice generation)
     */
    public function getByLevel($programmeId, $level)
    {
        $academicYear = AcademicYear::where('is_active', true)->first();

        if (!$academicYear) {
            return response()->json([
                'success' => false,
                'message' => 'No active academic year found'
            ], 404);
        }

        $feeStructure = ProgrammeFee::where('programme_id', $programmeId)
            ->where('academic_year_id', $academicYear->id)
            ->where('level', $level)
            ->where('is_active', true)
            ->first();

        if (!$feeStructure) {
            return response()->json([
                'success' => false,
                'message' => 'Fee structure not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'id' => $feeStructure->id,
            'registration_fee' => $feeStructure->registration_fee,
            'semester_1_fee' => $feeStructure->semester_1_fee,
            'semester_2_fee' => $feeStructure->semester_2_fee,
            'total' => $feeStructure->registration_fee + $feeStructure->semester_1_fee + $feeStructure->semester_2_fee
        ]);
    }

    /**
     * Export fee structures to CSV (READ-ONLY)
     */
    public function export()
    {
        $feeStructures = ProgrammeFee::with(['programme', 'academicYear'])
            ->get();

        $filename = 'fee-structures-' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($feeStructures) {
            $file = fopen('php://output', 'w');

            // Headers
            fputcsv($file, [
                'Programme',
                'Programme Code',
                'Academic Year',
                'Level',
                'Registration Fee',
                'Semester 1 Fee',
                'Semester 2 Fee',
                'Total Annual Fee',
                'Status'
            ]);

            // Data
            foreach ($feeStructures as $fee) {
                fputcsv($file, [
                    $fee->programme->name ?? 'N/A',
                    $fee->programme->code ?? 'N/A',
                    $fee->academicYear->name ?? 'N/A',
                    'Year ' . $fee->level,
                    $fee->registration_fee,
                    $fee->semester_1_fee,
                    $fee->semester_2_fee,
                    $fee->registration_fee + $fee->semester_1_fee + $fee->semester_2_fee,
                    $fee->is_active ? 'Active' : 'Inactive'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}