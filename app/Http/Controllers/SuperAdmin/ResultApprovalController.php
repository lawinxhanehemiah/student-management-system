<?php

namespace App\Http\Controllers\SuperAdmin;

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
        $results = Result::whereIn('status', ['pending_hod', 'pending_academic', 'pending_principal'])
            ->with(['student.user', 'module', 'academicYear'])
            ->orderBy('updated_at', 'asc')
            ->paginate(20);

        return view('superadmin.approvals.results', compact('results'));
    }

    public function approve(Result $result)
    {
        if (!in_array($result->status, ['pending_hod', 'pending_academic', 'pending_principal'])) {
            return back()->with('error', 'Result cannot be approved at this stage.');
        }

        $this->resultService->approve($result);
        return back()->with('success', 'Result approved.');
    }

    public function reject(Request $request, Result $result)
    {
        $request->validate(['remarks' => 'nullable|string']);

        if ($result->status !== 'pending_hod') {
            return back()->with('error', 'Only results pending HOD can be rejected.');
        }

        $this->resultService->reject($result, $request->remarks);
        return back()->with('success', 'Result rejected.');
    }

    public function forcePublish(Result $result)
    {
        if ($result->status === 'published') {
            return back()->with('error', 'Result is already published.');
        }

        DB::transaction(function () use ($result) {
            $oldStatus = $result->status;
            $result->status = 'published';
            $result->approved_by = auth()->id();
            $result->approved_at = now();
            $result->save();

            ResultApproval::create([
                'result_id' => $result->id,
                'user_id' => auth()->id(),
                'action' => 'force_publish',
                'status_from' => $oldStatus,
                'status_to' => 'published',
                'comments' => 'Forced publish by Super Admin',
            ]);

            event(new \App\Events\ResultPublished($result));
        });

        return back()->with('success', 'Result force-published.');
    }
}