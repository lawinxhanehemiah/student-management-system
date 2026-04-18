<?php

namespace App\Http\Controllers\Principal;

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
        $results = Result::where('status', 'pending_principal')
            ->with(['student.user', 'module', 'academicYear'])
            ->paginate(20);

        return view('principal.results.pending', compact('results'));
    }

    public function publish(Request $request, Result $result)
    {
        $this->resultService->publish($result);
        return redirect()->back()->with('success', 'Result published.');
    }
}