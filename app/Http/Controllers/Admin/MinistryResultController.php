<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Imports\MinistryResultsImport;
use App\Services\ResultService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MinistryResultController extends Controller
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    public function importForm()
    {
        return view('admin.import.ministry');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|in:1,2',
        ]);

        $batchId = uniqid('ministry_', true);
        Excel::import(new MinistryResultsImport($request->academic_year_id, $request->semester, $batchId, $this->resultService), $request->file('file'));

        return redirect()->back()->with('success', 'Ministry results imported successfully.');
    }
}