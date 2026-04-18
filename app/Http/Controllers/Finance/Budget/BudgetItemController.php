<?php

namespace App\Http\Controllers\Finance\Budget;

use App\Http\Controllers\Controller;
use App\Models\BudgetYear;
use App\Models\Department;
use App\Models\BudgetItem;
use App\Models\BudgetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class BudgetItemController extends Controller
{
    /**
     * Display budget items
     */
    public function index(Request $request, $budgetId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        $query = BudgetItem::with(['department', 'category'])
            ->where('budget_year_id', $budgetId);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('category_id')) {
            $query->where('budget_category_id', $request->category_id);
        }

        $items = $query->orderBy('department_id')->paginate(15);

        $departments = Department::orderBy('name')->get();
        $categories = BudgetCategory::active()->orderBy('sort_order')->get();

        return view('finance.budget.items.index', compact(
            'budgetYear', 'items', 'departments', 'categories'
        ));
    }

    /**
     * Show form for creating budget item
     */
    public function create(Request $request, $budgetId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        if (!$budgetYear->isDraft()) {
            return redirect()->route('finance.budget.annual.show', $budgetId)
                ->with('error', 'Items can only be added to draft budgets');
        }

        $departments = Department::orderBy('name')->get();
        $categories = BudgetCategory::active()->orderBy('sort_order')->get();

        $selectedDepartment = $request->get('department_id');
        $selectedCategory = $request->get('category_id');

        return view('finance.budget.items.create', compact(
            'budgetYear', 'departments', 'categories', 'selectedDepartment', 'selectedCategory'
        ));
    }

    /**
     * Store budget item
     */
    public function store(Request $request, $budgetId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        if (!$budgetYear->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Items can only be added to draft budgets'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'budget_category_id' => 'required|exists:budget_categories,id',
            'description' => 'required|string|max:500',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'justification' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $totalAmount = $request->unit_price * $request->quantity;

            $item = BudgetItem::create([
                'budget_year_id' => $budgetId,
                'department_id' => $request->department_id,
                'budget_category_id' => $request->budget_category_id,
                'description' => $request->description,
                'unit_price' => $request->unit_price,
                'quantity' => $request->quantity,
                'total_amount' => $totalAmount,
                'justification' => $request->justification
            ]);

            // Update department budget allocation
            $departmentBudget = DepartmentBudget::firstOrCreate(
                [
                    'budget_year_id' => $budgetId,
                    'department_id' => $request->department_id,
                    'budget_category_id' => $request->budget_category_id
                ],
                [
                    'allocated_amount' => 0,
                    'utilized_amount' => 0,
                    'remaining_amount' => 0,
                    'percentage_utilized' => 0
                ]
            );

            $departmentBudget->addAllocation($totalAmount);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget item created successfully',
                'item' => $item
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget item creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create budget item'
            ], 500);
        }
    }

    /**
     * Update budget item
     */
    public function update(Request $request, $budgetId, $itemId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        if (!$budgetYear->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Items can only be updated in draft budgets'
            ], 422);
        }

        $item = BudgetItem::findOrFail($itemId);

        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:500',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'justification' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $oldTotal = $item->total_amount;
            $newTotal = $request->unit_price * $request->quantity;

            $item->update([
                'description' => $request->description,
                'unit_price' => $request->unit_price,
                'quantity' => $request->quantity,
                'total_amount' => $newTotal,
                'justification' => $request->justification
            ]);

            // Update department budget allocation
            $departmentBudget = DepartmentBudget::where('budget_year_id', $budgetId)
                ->where('department_id', $item->department_id)
                ->where('budget_category_id', $item->budget_category_id)
                ->first();

            if ($departmentBudget) {
                $departmentBudget->addAllocation($newTotal - $oldTotal);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget item updated successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget item update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update budget item'
            ], 500);
        }
    }

    /**
     * Delete budget item
     */
    public function destroy($budgetId, $itemId)
    {
        $budgetYear = BudgetYear::findOrFail($budgetId);

        if (!$budgetYear->isDraft()) {
            return response()->json([
                'success' => false,
                'message' => 'Items can only be deleted in draft budgets'
            ], 422);
        }

        try {
            DB::beginTransaction();

            $item = BudgetItem::findOrFail($itemId);
            $amount = $item->total_amount;

            // Update department budget allocation
            $departmentBudget = DepartmentBudget::where('budget_year_id', $budgetId)
                ->where('department_id', $item->department_id)
                ->where('budget_category_id', $item->budget_category_id)
                ->first();

            if ($departmentBudget) {
                $departmentBudget->addAllocation(-$amount);
            }

            $item->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget item deleted successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Budget item deletion failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete budget item'
            ], 500);
        }
    }
}