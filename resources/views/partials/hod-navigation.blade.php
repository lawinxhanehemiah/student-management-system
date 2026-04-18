@php
    use App\Helpers\Navigation;
    
    // Initialize navigation variable
    $user = Auth::user();
    $navigation = [];
    $badgeData = [];
    
    if ($user) {
        // Get navigation from config
        $config = config('navigation.hod', []);
        
        // If config is empty, show message in logs
        if (empty($config)) {
            \Log::warning('Navigation config is empty for hod');
        }
        
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
    
    $currentPath = request()->path();
@endphp

{{-- Debug: Uncomment kuona kama navigation ipo --}}
{{-- @dump($navigation) --}}

@forelse($navigation as $item)
    @if(isset($item['is_header']) && $item['is_header'])
        <!-- Header Item -->
        <li class="nxl-item nxl-caption">
            <label>{{ $item['title'] }}</label>
        </li>
    @elseif(isset($item['divider']) && $item['divider'])
        <!-- Divider -->
        <li class="nxl-item dropdown-divider"></li>
    @elseif(isset($item['children']) && count($item['children']) > 0)
        <!-- Parent Menu with Children -->
        @php
            $hasActiveChild = false;
            foreach($item['children'] as $child) {
                if(isset($child['route']) && $child['route'] !== '#') {
                    if(Navigation::routeExists($child['route']) && Navigation::isActive($child['route'], $currentPath)) {
                        $hasActiveChild = true;
                        break;
                    }
                }
            }
        @endphp
        <li class="nxl-item nxl-hasmenu hod-menu-item {{ $hasActiveChild ? 'active' : '' }}">
            <a href="javascript:void(0);" class="nxl-link">
                <span class="nxl-micon"><i class="{{ $item['icon'] }}"></i></span>
                <span class="nxl-mtext">{{ $item['title'] }}</span>
                <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
            </a>
            <ul class="nxl-submenu">
                @foreach($item['children'] as $child)
                    @if(isset($child['divider']) && $child['divider'])
                        <li class="dropdown-divider"></li>
                    @elseif(isset($child['is_header']) && $child['is_header'])
                        <li class="nxl-item nxl-caption">
                            <label>{{ $child['header'] }}</label>
                        </li>
                    @else
                        @if(Navigation::routeExists($child['route']))
                            @php
                                $isActive = Navigation::isActive($child['route'], $currentPath);
                                $badgeValue = isset($child['badge']) && isset($badgeData[$child['badge']]) 
                                    ? $badgeData[$child['badge']] 
                                    : 0;
                            @endphp
                            <li class="nxl-item {{ $isActive ? 'active' : '' }}">
                                <a class="nxl-link" href="{{ $child['route'] === '#' ? 'javascript:void(0);' : route($child['route']) }}">
                                    <i class="{{ $child['icon'] }} me-2"></i>{{ $child['label'] }}
                                    @if($badgeValue > 0)
                                        <span class="badge bg-danger ms-2">{{ $badgeValue }}</span>
                                    @endif
                                </a>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        </li>
    @else
        <!-- Single Menu Item -->
        @if(Navigation::routeExists($item['route']))
            @php
                $isActive = Navigation::isActive($item['route'], $currentPath);
                $badgeValue = isset($item['badge']) && isset($badgeData[$item['badge']]) 
                    ? $badgeData[$item['badge']] 
                    : 0;
            @endphp
            <li class="nxl-item hod-menu-item {{ $isActive ? 'active' : '' }}">
                <a class="nxl-link" href="{{ $item['route'] === '#' ? 'javascript:void(0);' : route($item['route']) }}">
                    <span class="nxl-micon"><i class="{{ $item['icon'] }}"></i></span>
                    <span class="nxl-mtext">{{ $item['title'] }}</span>
                    @if($badgeValue > 0)
                        <span class="badge bg-danger ms-2">{{ $badgeValue }}</span>
                    @endif
                </a>
            </li>
        @endif
    @endif
@empty
    <!-- Default menu when no navigation config -->
    <li class="nxl-item">
        <a class="nxl-link" href="{{ route('hod.dashboard') }}">
            <span class="nxl-micon"><i class="feather-home"></i></span>
            <span class="nxl-mtext">Dashboard</span>
        </a>
    </li>
    <li class="nxl-item">
        <a class="nxl-link" href="{{ route('hod.students') }}">
            <span class="nxl-micon"><i class="feather-users"></i></span>
            <span class="nxl-mtext">Students</span>
        </a>
    </li>
@endforelse