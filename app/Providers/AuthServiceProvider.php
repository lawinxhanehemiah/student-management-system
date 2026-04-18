<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [];

    public function boot(): void
    {
        // SuperAdmin anaruhusiwa kila kitu (override)
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('SuperAdmin')) {
                return true;
            }
        });

        // Financial Controller anaweza kuunda ombi
        Gate::define('create-adjustment-request', function ($user) {
            return $user->hasRole('Financial_Controller');
        });

        // Principal anaweza kuidhinisha
        Gate::define('approve-adjustment-request', function ($user) {
            return $user->hasRole('Principal');
        });

        // Super Admin anaweza kusimamia yote
        Gate::define('manage-all-requests', function ($user) {
            return $user->hasRole('SuperAdmin');
        });
    }
}