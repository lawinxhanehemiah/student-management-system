<?php
// app/Providers/NavigationServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Helpers\Navigation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NavigationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Hakuna kwenye register
    }

    public function boot(): void
    {
        // Tumia try-catch kuzuia errors
        try {
            View::composer(['layouts.hod', 'partials.hod-navigation'], function ($view) {
                $user = Auth::user();
                $navigation = [];
                $badgeData = [];
                
                if ($user) {
                    $config = config('navigation.hod', []);
                    
                    $badgeData = [
                        'pendingApprovalsCount' => $user->pending_approvals_count ?? 0,
                        'pendingRequisitionsCount' => $user->pending_requisitions_count ?? 0,
                        'pendingLeaveCount' => $user->pending_leave_count ?? 0,
                        'pendingResultsCount' => $user->pending_results_count ?? 0,
                        'pendingPromotionCount' => $user->pending_promotion_count ?? 0,
                        'pendingBudgetCount' => $user->pending_budget_count ?? 0,
                        'notificationCount' => $user->unreadNotifications->count() ?? 0,
                    ];
                    
                    $navigation = Navigation::buildNavigation($config, $badgeData);
                }
                
                $view->with('navigation', $navigation);
                $view->with('badgeData', $badgeData);
            });
        } catch (\Exception $e) {
            Log::error('NavigationServiceProvider error: ' . $e->getMessage());
        }
    }
}