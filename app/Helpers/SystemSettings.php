<?php

if (!function_exists('getSystemSetting')) {
    function getSystemSetting($key, $default = null)
    {
        $settings = Cache::remember('system_settings', 3600, function () {
            return \App\Models\SystemSetting::pluck('setting_value', 'setting_key')->toArray();
        });
        
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('getAcademicYears')) {
    function getAcademicYears()
    {
        $yearsJson = getSystemSetting('academic_years', '[]');
        return json_decode($yearsJson, true) ?: [];
    }
}

if (!function_exists('getCurrentAcademicYear')) {
    function getCurrentAcademicYear()
    {
        $years = getAcademicYears();
        $currentId = (int) getSystemSetting('current_academic_year', 0);
        
        foreach ($years as $year) {
            if ($year['id'] == $currentId) {
                return (object) $year;
            }
        }
        
        // Return first active or first year
        foreach ($years as $year) {
            if ($year['is_active']) {
                return (object) $year;
            }
        }
        
        return count($years) > 0 ? (object) $years[0] : null;
    }
}

if (!function_exists('getFiscalYears')) {
    function getFiscalYears()
    {
        $yearsJson = getSystemSetting('fiscal_years', '[]');
        return json_decode($yearsJson, true) ?: [];
    }
}

if (!function_exists('getCurrentFiscalYear')) {
    function getCurrentFiscalYear()
    {
        $years = getFiscalYears();
        $currentId = (int) getSystemSetting('current_fiscal_year', 0);
        
        foreach ($years as $year) {
            if ($year['id'] == $currentId) {
                return (object) $year;
            }
        }
        
        // Return first active or first year
        foreach ($years as $year) {
            if ($year['is_active']) {
                return (object) $year;
            }
        }
        
        return count($years) > 0 ? (object) $years[0] : null;
    }
}