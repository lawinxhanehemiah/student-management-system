<?php

namespace App\Http\Controllers\Finance\Asset;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\User;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with(['category', 'department', 'assignedTo']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_tag', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%");
            });
        }

        $assets = $query->orderBy('created_at', 'desc')->paginate(20);

        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $statuses = ['active', 'under_maintenance', 'disposed', 'transferred', 'pending'];

        $stats = [
            'total_assets' => Asset::count(),
            'total_value' => Asset::sum('current_value'),
            'active_assets' => Asset::where('status', 'active')->count(),
            'under_maintenance' => Asset::where('status', 'under_maintenance')->count(),
        ];

        return view('finance.asset.index', compact('assets', 'categories', 'departments', 'statuses', 'stats'));
    }

    public function create()
    {
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $users = User::orderBy('first_name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        $lastAsset = Asset::orderBy('id', 'desc')->first();
        $nextId = $lastAsset ? $lastAsset->id + 1 : 1;
        $assetTag = 'AST-' . date('Y') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);

        return view('finance.asset.create', compact('categories', 'departments', 'users', 'suppliers', 'assetTag'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'asset_tag' => 'required|string|unique:assets',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|unique:assets',
            'category_id' => 'required|exists:asset_categories,id',
            'department_id' => 'nullable|exists:departments,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'location' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $asset = Asset::create([
                'asset_tag' => $request->asset_tag,
                'name' => $request->name,
                'serial_number' => $request->serial_number,
                'category_id' => $request->category_id,
                'department_id' => $request->department_id,
                'supplier_id' => $request->supplier_id,
                'purchase_date' => $request->purchase_date,
                'purchase_cost' => $request->purchase_cost,
                'current_value' => $request->purchase_cost,
                'salvage_value' => $request->salvage_value ?? 0,
                'useful_life_years' => $request->useful_life_years,
                'warranty_expiry' => $request->warranty_expiry,
                'location' => $request->location,
                'assigned_to' => $request->assigned_to,
                'description' => $request->description,
                'status' => 'active',
                'created_by' => Auth::id()
            ]);

            DB::commit();

            return redirect()->route('finance.asset.assets.show', $asset->id)
                ->with('success', 'Asset created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create asset: ' . $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $asset = Asset::with([
            'category', 'department', 'supplier', 'assignedTo', 'creator',
            'depreciations' => function($q) { $q->latest()->limit(12); },
            'transfers' => function($q) { $q->latest()->limit(5); },
            'disposals'
        ])->findOrFail($id);

        return view('finance.asset.show', compact('asset'));
    }

    public function edit($id)
    {
        $asset = Asset::findOrFail($id);

        if ($asset->isDisposed()) {
            return redirect()->route('finance.asset.assets.show', $id)
                ->with('error', 'Disposed assets cannot be edited');
        }

        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $users = User::orderBy('first_name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('finance.asset.edit', compact('asset', 'categories', 'departments', 'users', 'suppliers'));
    }

    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);

        if ($asset->isDisposed()) {
            return redirect()->back()->with('error', 'Disposed assets cannot be updated');
        }

        $validator = Validator::make($request->all(), [
            'asset_tag' => 'required|string|unique:assets,asset_tag,' . $id,
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|unique:assets,serial_number,' . $id,
            'category_id' => 'required|exists:asset_categories,id',
            'department_id' => 'nullable|exists:departments,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'salvage_value' => 'nullable|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'location' => 'nullable|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'status' => 'required|in:active,under_maintenance,disposed,transferred,pending',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();
            $asset->update($request->all());
            DB::commit();

            return redirect()->route('finance.asset.assets.show', $id)
                ->with('success', 'Asset updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to update asset')->withInput();
        }
    }

    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);

        if (!$asset->isDisposed()) {
            return redirect()->back()->with('error', 'Only disposed assets can be deleted');
        }

        try {
            $asset->delete();
            return redirect()->route('finance.asset.assets.index')->with('success', 'Asset deleted successfully');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete asset');
        }
    }

    public function calculateDepreciation($id)
    {
        $asset = Asset::findOrFail($id);

        if ($asset->isDisposed()) {
            return response()->json(['success' => false, 'message' => 'Cannot calculate depreciation for disposed asset']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'annual_depreciation' => $asset->calculateAnnualDepreciation(),
                'monthly_depreciation' => $asset->calculateMonthlyDepreciation(),
                'age_in_years' => $asset->getAgeInYears(),
                'remaining_life_years' => $asset->getRemainingLifeYears(),
                'depreciation_percentage' => $asset->getDepreciationPercentage(),
                'current_value' => $asset->current_value,
                'purchase_cost' => $asset->purchase_cost
            ]
        ]);
    }

    public function export(Request $request)
    {
        $query = Asset::with(['category', 'department']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assets = $query->orderBy('asset_tag')->get();

        $filename = 'assets-' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($assets) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Asset Tag', 'Name', 'Category', 'Serial Number', 'Department', 'Purchase Date', 'Purchase Cost', 'Current Value', 'Status', 'Location']);
            
            foreach ($assets as $asset) {
                fputcsv($file, [
                    $asset->asset_tag,
                    $asset->name,
                    $asset->category->name ?? 'N/A',
                    $asset->serial_number ?? 'N/A',
                    $asset->department->name ?? 'N/A',
                    $asset->purchase_date->format('Y-m-d'),
                    $asset->purchase_cost,
                    $asset->current_value,
                    $asset->status,
                    $asset->location ?? 'N/A'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}