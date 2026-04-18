<?php

namespace App\Http\Controllers\Finance\Budget;

use App\Http\Controllers\Controller;
use App\Models\BudgetYear;
use App\Models\Department;
use App\Models\DepartmentBudget;
use App\Models\BudgetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DepartmentBudgetController extends Controller
{
    /**
     * Show department budget allocation
     */
    public function index(Request $request, $budgetId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        $query = DepartmentBudget::with(['department', 'category'])
            ->where('budget_year_id', $budgetId);

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('budget_category_id', $request->category_id);
        }

        $allocations = $query->orderBy('department_id')->paginate(15);

        $departments = Department::orderBy('name')->get();
        $categories = BudgetCategory::active()->orderBy('sort_order')->get();

        return view('finance.budget.department.index', compact(
            'budgetYear', 'allocations', 'departments', 'categories'
        ));
    }

    /**
     * Show form for allocating department budget
     */
    public function create($budgetId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        if (!$budgetYear->isDraft()) {
            return redirect()->route('finance.budget.annual.show', $budgetId)
                ->with('error', 'Allocations can only be added to draft budgets');
        }

        $departments = Department::orderBy('name')->get();
        $categories = BudgetCategory::active()->orderBy('sort_order')->get();

        return view('finance.budget.department.create', compact(
            'budgetYear', 'departments', 'categories'
        ));
    }

    /**
     * Store department budget allocation
     */
    public function store(Request $request, $budgetId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        if (!$budgetYear->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Allocations can only be added to draft budgets'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'allocations' => 'required|array',
            'allocations.*.department_id' => 'required|exists:departments,id',
            'allocations.*.category_id' => 'required|exists:budget_categories,id',
            'allocations.*.amount' => 'required|numeric|min:0',
            'allocations.*.notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $totalAllocated = 0;

            foreach ($request->allocations as $alloc) {
                // Check if allocation already exists
                $exists = DepartmentBudget::where('budget_year_id', $budgetId)
                    ->where('department_id', $alloc['department_id'])
                    ->where('budget_category_id', $alloc['category_id'])
                    ->exists();

                if ($exists) {
                    throw new \Exception('Allocation already exists for this department and category');
                }

                $departmentBudget = DepartmentBudget::create([
                    'budget_year_id' => $budgetId,
                    'department_id' => $alloc['department_id'],
                    'budget_category_id' => $alloc['category_id'],
                    'allocated_amount' => $alloc['amount'],
                    'utilized_amount' => 0,
                    'remaining_amount' => $alloc['amount'],
                    'percentage_utilized' => 0,
                    'notes' => $alloc['notes'] ?? null
                ]);

                $totalAllocated += $alloc['amount'];
            }

            // Update budget year totals
            $budgetYear->total_allocated += $totalAllocated;
            $budgetYear->total_remaining = $budgetYear->total_budget - $budgetYear->total_allocated;
            $budgetYear->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Department allocations created successfully',
                'redirect_url' => route('finance.budget.annual.show', $budgetId)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Department allocation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create allocations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update department budget allocation
     */
    public function update(Request $request, $budgetId, $allocationId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        if (!$budgetYear->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Allocations can only be updated in draft budgets'
            ], 422);
        }

        $allocation = DepartmentBudget::findOrFail($allocationId);

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldAmount = $allocation->allocated_amount;
            $newAmount = $request->amount;

            $allocation->update([
                'allocated_amount' => $newAmount,
                'remaining_amount' => $newAmount - $allocation->utilized_amount,
                'notes' => $request->notes ?? $allocation->notes
            ]);

            // Update budget year totals
            $budgetYear->total_allocated = $budgetYear->total_allocated - $oldAmount + $newAmount;
            $budgetYear->total_remaining = $budgetYear->total_budget - $budgetYear->total_allocated;
            $budgetYear->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Allocation updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Allocation update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update allocation'
            ], 500);
        }
    }

    /**
     * Delete department budget allocation
     */
    public function destroy($budgetId, $allocationId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        if (!$budgetYear->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Allocations can only be deleted in draft budgets'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $allocation = DepartmentBudget::findOrFail($allocationId);
            $amount = $allocation->allocated_amount;

            $allocation->delete();

            // Update budget year totals
            $budgetYear->total_allocated -= $amount;
            $budgetYear->total_remaining = $budgetYear->total_budget - $budgetYear->total_allocated;
            $budgetYear->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Allocation deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Allocation deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete allocation'
            ], 500);
        }
    }

    /**
     * Get department budget details
     */
    public function show($budgetId, $allocationId)
    {
        $allocation = DepartmentBudget::with([
            'budgetYear',
            'department',
            'category',
            'items'
        ])->findOrFail($allocationId);

        return view('finance.budget.department.show', compact('allocation'));
    }
}