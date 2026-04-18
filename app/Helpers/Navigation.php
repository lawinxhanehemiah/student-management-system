<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Models\Department;

class Navigation
{
    /**
     * Check if user has permission
     */
    public static function hasPermission($permission)
    {
        if (!$permission) return true;
        
        $user = Auth::user();
        if (!$user) return false;
        
        // =============================================
        // HEALTH DEPARTMENT PERMISSION
        // =============================================
        if ($permission === 'health-department') {
            // Angalia kama user yuko kwenye health departments
            // Health departments: PST (12), CMT (13)
            $healthDeptIds = [12, 13]; // PST na CMT
            $healthDeptCodes = ['PST', 'CMT'];
            
            // Kwa kutumia department_id
            if (in_array($user->department_id, $healthDeptIds)) {
                return true;
            }
            
            // Kwa kutumia department code
            if ($user->department && in_array($user->department->code, $healthDeptCodes)) {
                return true;
            }
            
            return false;
        }
        
        // =============================================
        // PHARMACY SPECIFIC (PST)
        // =============================================
        if ($permission === 'pharmacy-department') {
            return ($user->department_id == 12) || 
                   ($user->department && $user->department->code == 'PST');
        }
        
        // =============================================
        // CLINICAL MEDICINE SPECIFIC (CMT)
        // =============================================
        if ($permission === 'clinical-department') {
            return ($user->department_id == 13) || 
                   ($user->department && $user->department->code == 'CMT');
        }
        
        // Default permission check (kwa sasa tumerudi true)
        return true;
    }

    /**
     * Check if route exists
     */
    public static function routeExists($route)
    {
        if (!$route || $route === '#') return true;
        return Route::has($route);
    }

    /**
     * Check if menu item is active
     */
    public static function isActive($route, $currentPath)
    {
        if (!$route || $route === '#') return false;
        
        try {
            $routePath = route($route, [], false);
            $routePath = ltrim($routePath, '/');
            return str_contains($currentPath, $routePath) || $currentPath === $routePath;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Filter menu items by permission
     */
    public static function filterMenu($items, $data = [])
    {
        $filtered = [];
        
        foreach ($items as $item) {
            // Check permission for main item
            if (isset($item['permission']) && !self::hasPermission($item['permission'])) {
                continue;
            }
            
            // Process children if exists
            if (isset($item['children'])) {
                $item['children'] = self::filterMenu($item['children'], $data);
                
                // If no children left after filtering, skip this parent
                if (empty($item['children'])) {
                    continue;
                }
            }
            
            // Check if route exists
            if (isset($item['route']) && !self::routeExists($item['route']) && $item['route'] !== '#') {
                continue;
            }
            
            $filtered[] = $item;
        }
        
        return $filtered;
    }

    /**
     * Build navigation tree
     */
    public static function buildNavigation($items, $data = [])
    {
        return self::filterMenu($items, $data);
    }
}