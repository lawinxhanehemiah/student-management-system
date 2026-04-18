<?php

namespace App\Http\Controllers\Finance\Budget;

use App\Http\Controllers\Controller;
use App\Models\BudgetYear;
use App\Models\BudgetRevision;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class BudgetRevisionController extends Controller
{
    /**
     * Display budget revisions
     */
    public function index(Request $request, $budgetId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        $query = BudgetRevision::with(['department', 'requester', 'approver'])
            ->where('budget_year_id', $budgetId);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $revisions = $query->orderBy('created_at', 'desc')->paginate(15);

        $departments = Department::orderBy('name')->get();

        return view('finance.budget.revisions.index', compact(
            'budgetYear', 'revisions', 'departments'
        ));
    }

    /**
     * Show form for creating revision
     */
    public function create($budgetId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        if (!$budgetYear->isActive()) {
            return redirect()->route('finance.budget.annual.show', $budgetId)
                ->with('error', 'Revisions can only be made to active budgets');
        }

        $departments = Department::orderBy('name')->get();

        return view('finance.budget.revisions.create', compact('budgetYear', 'departments'));
    }

    /**
     * Store revision request
     */
    public function store(Request $request, $budgetId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        if (!$budgetYear->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Revisions can only be made to active budgets'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'type' => 'required|in:increase,decrease,transfer',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:500',
            'target_department_id' => 'required_if:type,transfer|exists:departments,id',
            'target_category_id' => 'nullable|exists:budget_categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Get current allocation for this department
            $currentAllocation = $budgetYear->departmentBudgets()
                ->where('department_id', $request->department_id)
                ->sum('allocated_amount');

            $revision = BudgetRevision::create([
                'revision_number' => BudgetRevision::generateRevisionNumber(),
                'budget_year_id' => $budgetId,
                'department_id' => $request->department_id,
                'type' => $request->type,
                'amount' => $request->amount,
                'reason' => $request->reason,
                'status' => 'pending',
                'old_values' => [
                    'allocation' => $currentAllocation
                ],
                'requested_by' => Auth::id(),
                'metadata' => [
                    'target_department' => $request->target_department_id ?? null,
                    'target_category' => $request->target_category_id ?? null
                ]
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget revision request submitted successfully',
                'revision' => $revision
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget revision creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit revision request'
            ], 500);
        }
    }

    /**
     * Approve revision
     */
    public function approve(Request $request, $budgetId, $revisionId)
    {
        try {
            $revision = BudgetRevision::where('budget_year_id', $budgetId)
                ->findOrFail($revisionId);

            if (!$revision->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This revision has already been processed'
                ], 422);
            }

            DB::beginTransaction();

            // Update the revision
            $revision->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now()
            ]);

            // Here you would implement the actual budget changes
            // based on revision type (increase, decrease, transfer)

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget revision approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget revision approval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve revision'
            ], 500);
        }
    }

    /**
     * Reject revision
     */
    public function reject(Request $request, $budgetId, $revisionId)
    {
        try {
            $revision = BudgetRevision::where('budget_year_id', $budgetId)
                ->findOrFail($revisionId);

            if (!$revision->isPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This revision has already been processed'
                ], 422);
            }

            $validator = Validator::make($request->all(), [
                'rejection_reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            $revision->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget revision rejected'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget revision rejection failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject revision'
            ], 500);
        }
    }
}