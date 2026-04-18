<?php

namespace App\Http\Controllers\Finance\Procurement;

use App\Http\Controllers\Controller;
use App\Models\ApprovalLevel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class ApprovalLevelController extends Controller
{
    public function index(Request $request)
    {
        $query = ApprovalLevel::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }

        $levels = $query->orderBy('level_order')->paginate(15);
        $roles = Role::all();
        $users = User::orderBy('first_name')->get();

        return view('finance.procurement.approval-levels.index', compact('levels', 'roles', 'users'));
    }

    public function create()
    {
        $roles = Role::all();
        $users = User::orderBy('first_name')->get();
        return view('finance.procurement.approval-levels.create', compact('roles', 'users'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:approval_levels',
            'level_order' => 'required|integer|unique:approval_levels',
            'approver_type' => 'required|in:role,user,department_head',
            'approver_value' => 'required|string',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gte:min_amount',
            'is_active' => 'boolean',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            ApprovalLevel::create([
                'name' => $request->name,
                'code' => $request->code,
                'level_order' => $request->level_order,
                'approver_type' => $request->approver_type,
                'approver_value' => $request->approver_value,
                'min_amount' => $request->min_amount,
                'max_amount' => $request->max_amount,
                'is_active' => $request->boolean('is_active', true),
                'description' => $request->description
            ]);

            return redirect()->route('finance.procurement.approval-levels.index')
                ->with('success', 'Approval level created successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create approval level')->withInput();
        }
    }

    public function edit($id)
    {
        $level = ApprovalLevel::findOrFail($id);
        $roles = Role::all();
        $users = User::orderBy('first_name')->get();
        return view('finance.procurement.approval-levels.edit', compact('level', 'roles', 'users'));
    }

    public function update(Request $request, $id)
    {
        $level = ApprovalLevel::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:approval_levels,code,' . $id,
            'level_order' => 'required|integer|unique:approval_levels,level_order,' . $id,
            'approver_type' => 'required|in:role,user,department_head',
            'approver_value' => 'required|string',
            'min_amount' => 'required|numeric|min:0',
            'max_amount' => 'nullable|numeric|gte:min_amount',
            'is_active' => 'boolean',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $level->update([
                'name' => $request->name,
                'code' => $request->code,
                'level_order' => $request->level_order,
                'approver_type' => $request->approver_type,
                'approver_value' => $request->approver_value,
                'min_amount' => $request->min_amount,
                'max_amount' => $request->max_amount,
                'is_active' => $request->boolean('is_active', true),
                'description' => $request->description
            ]);

            return redirect()->route('finance.procurement.approval-levels.index')
                ->with('success', 'Approval level updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update approval level')->withInput();
        }
    }

    public function destroy($id)
    {
        $level = ApprovalLevel::findOrFail($id);

        if ($level->requisitionApprovals()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete level with existing approvals');
        }

        $level->delete();
        return redirect()->route('finance.procurement.approval-levels.index')
            ->with('success', 'Approval level deleted');
    }

    public function toggleStatus($id)
    {
        $level = ApprovalLevel::findOrFail($id);
        $level->is_active = !$level->is_active;
        $level->save();

        return redirect()->back()->with('success', 'Status updated successfully');
    }
}