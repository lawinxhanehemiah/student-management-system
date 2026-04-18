<?php

namespace App\Http\Controllers\Finance\Budget;

use App\Http\Controllers\Controller;
use App\Models\BudgetYear;
use App\Models\Department;
use App\Models\BudgetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;


class BudgetYearController extends Controller
{
    /**
     * Display a listing of budget years
     */
    public function index(Request $request)
    {
        $query = BudgetYear::with(['creator', 'approver']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('name', 'LIKE', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $budgetYears = $query->orderBy('start_date', 'desc')->paginate(15);

        return view('finance.budget.annual.index', compact('budgetYears'));
    }

    /**
     * Show form for creating new budget year
     */
    public function create()
    {
        $nextName = BudgetYear::generateName();
        return view('finance.budget.annual.create', compact('nextName'));
    }

    /**
     * Store a newly created budget year
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:budget_years,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_budget' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $budgetYear = BudgetYear::create([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_budget' => $request->total_budget,
                'notes' => $request->notes,
                'status' => 'draft',
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('finance.budget.annual.show', $budgetYear->id)
                ->with('success', 'Budget year created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget year creation failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to create budget year')
                ->withInput();
        }
    }

    /**
     * Display the specified budget year
     */
    public function show($id)
    {
        $budgetYear = BudgetYear::with([
            'creator',
            'approver',
            'departmentBudgets.department',
            'departmentBudgets.category',
            'approvals'
        ])->findOrFail($id);

        // Get departments with budget allocation
        $departments = Department::orderBy('name')->get();

        // Get categories
        $categories = BudgetCategory::active()->orderBy('sort_order')->get();

        // Calculate summary
        $summary = [
            'total_departments' => $budgetYear->departmentBudgets->count(),
            'total_categories' => $budgetYear->departmentBudgets->groupBy('budget_category_id')->count(),
            'utilization_rate' => $budgetYear->total_allocated > 0 
                ? round(($budgetYear->total_utilized / $budgetYear->total_allocated) * 100, 2) 
                : 0,
            'remaining' => $budgetYear->total_allocated - $budgetYear->total_utilized
        ];

        return view('finance.budget.annual.show', compact('budgetYear', 'departments', 'categories', 'summary'));
    }

    /**
     * Show form for editing budget year
     */
    public function edit($id)
    {
        $budgetYear = BudgetYear::findOrFail($id);

        if (!$budgetYear->canBeEdited()) {
            return redirect()->route('finance.budget.annual.show', $id)
                ->with('error', 'This budget year cannot be edited');
        }

        return view('finance.budget.annual.edit', compact('budgetYear'));
    }

    /**
     * Update the specified budget year
     */
    public function update(Request $request, $id)
    {
        $budgetYear = BudgetYear::findOrFail($id);

        if (!$budgetYear->canBeEdited()) {
            return redirect()->back()
                ->with('error', 'This budget year cannot be edited');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:budget_years,name,' . $id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'total_budget' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $budgetYear->update([
                'name' => $request->name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_budget' => $request->total_budget,
                'notes' => $request->notes
            ]);

            DB::commit();

            return redirect()->route('finance.budget.annual.show', $id)
                ->with('success', 'Budget year updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget year update failed: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Failed to update budget year')
                ->withInput();
        }
    }

    /**
     * Submit for approval
     */
    public function submitForApproval($id)
    {
        try {
            $budgetYear = BudgetYear::findOrFail($id);

            if (!$budgetYear->canBeApproved()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Budget year cannot be submitted for approval'
                ], 422);
            }

            DB::beginTransaction();

            $budgetYear->update([
                'status' => 'active'
            ]);

            // Create approval records
            $levels = ['hod', 'finance', 'director'];
            foreach ($levels as $level) {
                $budgetYear->approvals()->create([
                    'level' => $level,
                    'status' => 'pending'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget year submitted for approval'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget year submission failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit budget year'
            ], 500);
        }
    }

    /**
     * Approve budget year
     */
    public function approve(Request $request, $id)
    {
        try {
            $budgetYear = BudgetYear::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'level' => 'required|in:hod,finance,director',
                'comments' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update approval
            $approval = $budgetYear->approvals()
                ->where('level', $request->level)
                ->first();

            if ($approval) {
                $approval->update([
                    'status' => 'approved',
                    'comments' => $request->comments,
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);
            }

            // Check if all levels approved
            $pendingCount = $budgetYear->approvals()
                ->where('status', 'pending')
                ->count();

            if ($pendingCount == 0) {
                $budgetYear->update([
                    'status' => 'active',
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget year approved successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget year approval failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve budget year'
            ], 500);
        }
    }

    /**
     * Reject budget year
     */
    public function reject(Request $request, $id)
    {
        try {
            $budgetYear = BudgetYear::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'level' => 'required|in:hod,finance,director',
                'rejection_reason' => 'required|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update approval
            $approval = $budgetYear->approvals()
                ->where('level', $request->level)
                ->first();

            if ($approval) {
                $approval->update([
                    'status' => 'rejected',
                    'comments' => $request->rejection_reason,
                    'approved_by' => Auth::id(),
                    'approved_at' => now()
                ]);
            }

            // Update budget year status
            $budgetYear->update([
                'status' => 'draft'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget year rejected'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget year rejection failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject budget year'
            ], 500);
        }
    }

    /**
     * Close budget year
     */
    public function close($id)
    {
        try {
            $budgetYear = BudgetYear::findOrFail($id);

            if (!$budgetYear->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active budget years can be closed'
                ], 422);
            }

            $budgetYear->update([
                'status' => 'closed'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Budget year closed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Budget year closure failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to close budget year'
            ], 500);
        }
    }

    /**
     * Get budget vs actual data
     */
    public function vsActual($id)
    {
        $budgetYear = BudgetYear::with([
            'departmentBudgets.department',
            'departmentBudgets.category'
        ])->findOrFail($id);

        // Get actual expenditures from various sources
        // This would typically come from actual transactions
        $actualData = $this->getActualExpenditure($budgetYear);

        return view('finance.budget.vs-actual.index', compact('budgetYear', 'actualData'));
    }

    /**
     * Get actual expenditure (to be implemented based on your transaction sources)
     */
    private function getActualExpenditure($budgetYear)
    {
        // This method should fetch actual expenditures from:
        // - Purchase Orders
        // - Payment Vouchers
        // - Supplier Invoices
        // - etc.
        
        return [];
    }
}