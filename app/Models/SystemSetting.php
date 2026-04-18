<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'setting_group',
        'display_name',
        'description',
        'options',
        'sort_order',
        'is_public',
    ];

    protected $casts = [
        'options' => 'array',
        'is_public' => 'boolean',
    ];

    /**
     * Get setting value by key
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set setting value
     */
    public static function setValue($key, $value)
    {
        $setting = self::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value]
        );
        return $setting;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup($group)
    {
        return self::where('setting_group', $group)
            ->orderBy('sort_order')
            ->get();
    }
}