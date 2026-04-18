<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Services\ResultService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    protected $resultService;

    public function __construct(ResultService $resultService)
    {
        $this->resultService = $resultService;
    }

    public function index()
    {
        $results = Result::where('status', 'pending_academic')
            ->with(['student.user', 'module', 'academicYear'])
            ->paginate(20);

        return view('academic.results.pending', compact('results'));
    }

    public function forwardToPrincipal(Request $request, Result $result)
    {
        $this->resultService->forwardToPrincipal($result);
        return redirect()->back()->with('success', 'Result forwarded to Principal.');
    }
}