<?php

namespace App\Http\Controllers\Finance\Asset;

use App\Http\Controllers\Controller;
use App\Models\AssetCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AssetCategoryController extends Controller
{
    /**
     * Display a listing of asset categories
     */
    public function index(Request $request)
    {
        $query = AssetCategory::withCount('assets');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('code', 'LIKE', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->orderBy('name')->paginate(15);

        return view('finance.asset.categories.index', compact('categories'));
    }

    /**
     * Show form for creating new category
     */
    public function create()
    {
        return view('finance.asset.categories.create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:asset_categories',
            'description' => 'nullable|string',
            'depreciation_method' => 'required|in:straight_line,declining_balance,none',
            'default_useful_life_years' => 'nullable|integer|min:1',
            'default_salvage_value_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $category = AssetCategory::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'depreciation_method' => $request->depreciation_method,
                'default_useful_life_years' => $request->default_useful_life_years,
                'default_salvage_value_percentage' => $request->default_salvage_value_percentage ?? 0,
                'is_active' => $request->boolean('is_active', true),
                'created_by' => Auth::id()
            ]);

            return redirect()->route('finance.asset.categories.show', $category->id)
                ->with('success', 'Asset category created successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to create category: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified category
     */
    public function show($id)
    {
        $category = AssetCategory::with(['assets', 'creator'])->findOrFail($id);
        
        // Calculate statistics
        $stats = [
            'total_assets' => $category->assets->count(),
            'total_value' => $category->assets->sum('current_value'),
            'active_assets' => $category->assets->where('status', 'active')->count(),
            'avg_age' => $category->assets->avg(function($asset) {
                return $asset->getAgeInYears();
            })
        ];

        return view('finance.asset.categories.show', compact('category', 'stats'));
    }

    /**
     * Show form for editing category
     */
    public function edit($id)
    {
        $category = AssetCategory::findOrFail($id);
        return view('finance.asset.categories.edit', compact('category'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        $category = AssetCategory::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:asset_categories,code,' . $id,
            'description' => 'nullable|string',
            'depreciation_method' => 'required|in:straight_line,declining_balance,none',
            'default_useful_life_years' => 'nullable|integer|min:1',
            'default_salvage_value_percentage' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $category->update([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'depreciation_method' => $request->depreciation_method,
                'default_useful_life_years' => $request->default_useful_life_years,
                'default_salvage_value_percentage' => $request->default_salvage_value_percentage ?? 0,
                'is_active' => $request->boolean('is_active', true)
            ]);

            return redirect()->route('finance.asset.categories.show', $id)
                ->with('success', 'Asset category updated successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to update category')
                ->withInput();
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        $category = AssetCategory::findOrFail($id);

        // Check if category has assets
        if ($category->assets()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Cannot delete category with associated assets');
        }

        try {
            $category->delete();
            return redirect()->route('finance.asset.categories.index')
                ->with('success', 'Asset category deleted successfully');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete category');
        }
    }
}