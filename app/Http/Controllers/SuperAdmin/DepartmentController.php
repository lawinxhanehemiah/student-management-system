<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('users')->paginate(15);
        return view('superadmin.departments.index', compact('departments'));
    }

    public function create()
    {
        return view('superadmin.departments.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments',
            'code' => 'required|string|max:50|unique:departments',
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Department::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('superadmin.departments.index')
            ->with('success', 'Department created successfully');
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        return view('superadmin.departments.edit', compact('department'));
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:departments,name,' . $id,
            'code' => 'required|string|max:50|unique:departments,code,' . $id,
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $department->update([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('superadmin.departments.index')
            ->with('success', 'Department updated successfully');
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        if ($department->users()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete department with users');
        }

        $department->delete();
        return redirect()->route('superadmin.departments.index')
            ->with('success', 'Department deleted successfully');
    }
}