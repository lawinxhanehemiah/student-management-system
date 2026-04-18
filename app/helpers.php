<?php
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

function setting($key, $default = null)
{
    return Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
        return SystemSetting::where('setting_key', $key)->value('setting_value') ?? $default;
    });
}

if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd/m/Y')
    {
        if (!$date) {
            return null;
        }
        
        try {
            return Carbon::parse($date)->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
}
