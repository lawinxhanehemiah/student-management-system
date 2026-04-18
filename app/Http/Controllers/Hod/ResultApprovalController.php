<?php

namespace App\Http\Controllers\Hod;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Services\ResultService;
use Illuminate\Http\Request;

class ResultApprovalController extends Controller
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    public function index()
    {
        $results = Result::where('status', 'pending_hod')
            ->whereHas('module', function($q) {
                $q->where('department_id', auth()->user()->department_id);
            })
            ->with(['student.user', 'module', 'academicYear'])
            ->paginate(20);

        return view('hod.results.pending', compact('results'));
    }

    public function approve(Request $request, Result $result)
    {
        $request->validate(['remarks' => 'nullable|string']);
        $this->resultService->approve($result, $request->remarks);
        return redirect()->back()->with('success', 'Result approved and forwarded to Academic.');
    }

    public function reject(Request $request, Result $result)
    {
        $request->validate(['remarks' => 'required|string']);
        $this->resultService->reject($result, $request->remarks);
        return redirect()->back()->with('success', 'Result rejected and returned to lecturer.');
    }
}