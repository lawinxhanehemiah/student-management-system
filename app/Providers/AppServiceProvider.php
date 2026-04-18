<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (Schema::hasTable('system_settings')) {
            try {
                $settings = Cache::remember(
                    'system_settings',
                    now()->addHour(),
                    fn () => SystemSetting::where('is_public', true)->get()
                );

                foreach ($settings as $setting) {
                    Config::set(
                        'settings.' . $setting->setting_key,
                        $setting->setting_value
                    );
                }
            } catch (\Throwable $e) {
                logger()->warning('Failed to load system settings', [
                    'exception' => $e->getMessage()
                ]);
            }
        }

        Livewire::component(
            'applications.application-form',
            \App\Http\Livewire\Applications\ApplicationForm::class
        );
    }
}
