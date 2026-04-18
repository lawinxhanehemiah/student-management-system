<?php

namespace App\Http\Controllers\SuperAdmin\Config;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        // Group settings by their groups
        $groups = SystemSetting::distinct('setting_group')
            ->orderBy('setting_group')
            ->pluck('setting_group');

        $settingsByGroup = [];
        foreach ($groups as $group) {
            $settingsByGroup[$group] = SystemSetting::where('setting_group', $group)
                ->orderBy('sort_order')
                ->get();
        }

        return view('superadmin.config.general', compact('settingsByGroup'));
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*' => 'nullable',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        foreach ($request->settings as $key => $value) {
            SystemSetting::where('setting_key', $key)
                ->update(['setting_value' => $value]);
        }

        // Clear settings cache
        Cache::forget('system_settings');

        return redirect()->route('superadmin.config.general')
            ->with('success', 'System settings updated successfully.');
    }

    /**
     * Handle file uploads for settings
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $path = $request->file('logo')->store('logos', 'public');
        
        SystemSetting::updateOrCreate(
            ['setting_key' => 'logo_path'],
            ['setting_value' => 'storage/' . $path]
        );

        Cache::forget('system_settings');

        return response()->json([
            'success' => true,
            'path' => 'storage/' . $path,
            'message' => 'Logo updated successfully'
        ]);
    }

    /**
     * Reset to default settings
     */
    public function resetToDefaults()
    {
        $seeder = new \Database\Seeders\SystemSettingsSeeder();
        $seeder->run();

        Cache::forget('system_settings');

        return redirect()->route('superadmin.config.general')
            ->with('success', 'Settings reset to defaults successfully.');
    }
}