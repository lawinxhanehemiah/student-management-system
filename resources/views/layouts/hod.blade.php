<!DOCTYPE html> 
<html lang="zxx">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="description" content="" />
    <meta name="keyword" content="" />
    <meta name="author" content="flexilecode" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!--! The above 6 meta tags *must* come first in the head; any other head content must come *after* these tags !-->
    <!--! BEGIN: Apps Title-->
    <title>St.Maximmiliancolbe College - Head of Department Panel</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/logo.webp') }}" />

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/vendors/css/daterangepicker.min.css') }}" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}" />
    
    @stack('styles')
    
    <style>
        /* Sidebar logo sizing */
        .m-header .logo-lg {
            width: 100px;
            height: 40px;
            object-fit: contain;
            display: inline-block;
        }

        .m-header .logo-sm {
            width: 80px;
            height: 80px;
            object-fit: contain;
            display: none;
        }

        /* Sidebar collapse behavior */
        .nxl-navigation.collapsed .logo-lg {
            display: none;
        }

        .nxl-navigation.collapsed .brand-name {
            display: none;
        }

        .nxl-navigation.collapsed .logo-sm {
            display: inline-block;
        }

        /* Brand styling */
        .m-header .b-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 0 15px;
        }

        /* Main Content Area with Independent Scrolling */
        .main-content {
            min-height: calc(100vh - 70px);
            margin-top: 70px;
            overflow-y: auto;
            padding: 20px;
            transition: all 0.3s ease;
        }

        /* MOBILE FIX */
        @media (max-width: 991.98px) {
            .nxl-navigation {
                position: fixed;
                left: -280px;
                top: 0;
                bottom: 0;
                width: 280px !important;
                z-index: 1050;
                transition: left 0.3s ease;
                background: #fff;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
                overflow-y: auto;
            }
            
            .nxl-navigation.mobile-open {
                left: 0;
            }
            
            .nxl-navigation-toggle {
                display: none !important;
            }
            
            .nxl-header {
                padding-left: 0 !important;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                z-index: 1040;
                background: #fff;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .mobile-menu-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1049;
                display: none;
            }
            
            .mobile-menu-overlay.active {
                display: block;
            }
            
            .main-content {
                margin-left: 0 !important;
                margin-top: 70px;
                padding: 15px;
                width: 100%;
            }
        }

        /* DESKTOP STYLING */
        @media (min-width: 992px) {
            .nxl-navigation:not(.collapsed) {
                width: 280px;
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                z-index: 1030;
                overflow-y: auto;
            }
            
            .nxl-navigation.collapsed {
                width: 70px;
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                z-index: 1030;
                overflow-y: auto;
            }
            
            .nxl-navigation {
                transition: width 0.3s ease;
            }
            
            .nxl-header {
                position: fixed;
                top: 0;
                right: 0;
                left: 280px;
                z-index: 1020;
                background: #fff;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                transition: left 0.3s ease;
            }
            
            body.sidebar-collapsed .nxl-header {
                left: 70px;
            }
            
            .main-content {
                margin-left: 280px;
                transition: margin-left 0.3s ease;
            }
            
            body.sidebar-collapsed .main-content {
                margin-left: 70px;
            }
            
            .mobile-menu-overlay {
                display: none !important;
            }
        }

        body {
            overflow-x: hidden;
            background: #f8f9fa;
        }

        .footer {
            padding: 20px;
            background: #fff;
            border-top: 1px solid #e9ecef;
            margin-top: auto;
        }
        
        .nxl-hasmenu.active .nxl-submenu {
            display: block !important;
        }
        
        .hod-menu-item.active > .nxl-link {
            background-color: rgba(46, 204, 113, 0.1);
            color: #2ecc71;
            border-left: 3px solid #2ecc71;
        }
        
        /* Department badge in header */
        .dept-badge {
            background-color: #2ecc71;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        /* Menu section headers */
        .nxl-caption label {
            color: #2ecc71;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Icon variants for different sections */
        .icon-students { color: #3498db; }
        .icon-academic { color: #9b59b6; }
        .icon-results { color: #e67e22; }
        .icon-finance { color: #27ae60; }
        .icon-staff { color: #f39c12; }
        .icon-research { color: #1abc9c; }
        .icon-reports { color: #e74c3c; }
        .icon-approvals { color: #2c3e50; }
        .icon-assets { color: #7f8c8d; }
    </style>
</head>

<body>
    @php
        $user = Auth::user();
        $department = $user->department ?? null;
        $departmentName = $department->name ?? 'Department';
        $departmentCode = $department->code ?? null;
        
        // Badge data from controller or default to 0
        $badgeData = [
            'pendingApprovalsCount' => $pendingApprovalsCount ?? 0,
            'pendingRequisitionsCount' => $pendingRequisitionsCount ?? 0,
            'pendingLeaveCount' => $pendingLeaveCount ?? 0,
            'pendingResultsCount' => $pendingResultsCount ?? 0,
            'pendingPromotionCount' => $pendingPromotionCount ?? 0,
            'pendingBudgetCount' => $pendingBudgetCount ?? 0,
            'notificationCount' => $notificationCount ?? 0,
        ];
        
        $avatar = $user->profile_photo 
            ? 'storage/profile_photos/' . $user->profile_photo 
            : 'assets/images/default.png';
    @endphp

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileOverlay"></div>
  
    <!--! ================================================================ !-->
    <!--! [Start] Navigation Menu !-->
    <!--! ================================================================ !-->
    <nav class="nxl-navigation">
        <div class="m-header py-3">
            <a href="#" class="b-brand d-flex align-items-center justify-content-center">
                <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo" class="logo logo-lg img-fluid" />
                <span class="brand-name fw-bold text-dark">HOD PANEL</span>
                <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo" class="logo logo-sm img-fluid" />
            </a>
        </div>

        <div class="navbar-content">
            <ul class="nxl-navbar">
                @include('partials.hod-navigation')
                
                <li class="nxl-item dropdown-divider"></li>

                <!-- Logout -->
                <li class="nxl-item">
                    <a class="nxl-link" href="#"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <span class="nxl-micon"><i class="feather-log-out"></i></span>
                        <span class="nxl-mtext">Logout</span>
                    </a>
                </li>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </ul>
        </div>
    </nav>
    <!--! ================================================================ !-->
    <!--! [End]  Navigation Menu !-->
    <!--! ================================================================ !-->
    
    <!--! ================================================================ !-->
    <!--! [Start] Header !-->
    <!--! ================================================================ !-->
    <header class="nxl-header">
        <div class="header-wrapper">
            <!--! [Start] Header Left !-->
            <div class="header-left d-flex align-items-center gap-4">
                <!--! [Start] nxl-head-mobile-toggler !-->
                <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                    <div class="hamburger hamburger--arrowturn">
                        <div class="hamburger-box">
                            <div class="hamburger-inner"></div>
                        </div>
                    </div>
                </a>
                <!--! [End] nxl-head-mobile-toggler !-->
                <!--! [Start] nxl-navigation-toggle !-->
                <div class="nxl-navigation-toggle">
                    <a href="javascript:void(0);" id="menu-mini-button">
                       <i class="feather-menu"></i>
                    </a>
                    <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                        <i class="feather-arrow-right"></i>
                    </a>
                </div>
                <!--! [End] nxl-navigation-toggle !-->
                <div class="ms-3">
                    <span class="dept-badge">
                        <i class="fas fa-building me-1"></i>
                        {{ $departmentName }}
                    </span>
                </div>
            </div>
            <!--! [End] Header Left !-->
            <!--! [Start] Header Right !-->
            <div class="header-right ms-auto">
                <div class="d-flex align-items-center">
                    
                    <div class="nxl-h-item dark-light-theme">
                        <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                            <i class="feather-moon"></i>
                        </a>
                        <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                            <i class="feather-sun"></i>
                        </a>
                    </div>
                    
                    <div class="dropdown nxl-h-item">
                        <a class="nxl-head-link me-3" data-bs-toggle="dropdown" href="#" role="button" data-bs-auto-close="outside">
                            <i class="feather-bell"></i>
                            <span class="badge bg-danger nxl-h-badge">3</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-menu">
                            <div class="d-flex justify-content-between align-items-center notifications-head">
                                <h6 class="fw-bold text-dark mb-0">Notifications</h6>
                                <a href="javascript:void(0);" class="fs-11 text-success text-end ms-auto" data-bs-toggle="tooltip" title="Make as Read">
                                    <i class="feather-check"></i>
                                    <span>Make as Read</span>
                                </a>
                            </div>
                            <div class="notifications-item">
                               
                                
                            </div>
                            
                            
                            <div class="text-center notifications-footer">
                                <a href="javascript:void(0);" class="fs-13 fw-semibold text-dark">Alls Notifications</a>
                            </div>
                        </div>
                    </div>
                    @php
    $user = Auth::user();
    
    if (!$user) {
        $avatar = asset('assets/images/avatar/1.png');
        $displayName = 'Guest User';
        $displayEmail = 'guest@example.com';
        $displayType = 'GUEST';
    } else {
        // MUHIMU: Angalia kama profile_photo ipo kwenye database
        if (!empty($user->profile_photo)) {
            // Tumia asset() moja kwa moja na storage path
            $avatar = asset('storage/' . $user->profile_photo);
            
            // Debug - unaweza kuona URL (remove baada ya kufanya kazi)
            // echo "<!-- Avatar URL: " . $avatar . " -->";
        } else {
            // Default UI Avatar
            $name = urlencode($user->first_name . ' ' . $user->last_name);
            $avatar = "https://ui-avatars.com/api/?name={$name}&color=7F9CF5&background=EBF4FF&size=128&bold=true";
        }
        
        $displayName = $user->first_name . ' ' . $user->last_name;
        $displayEmail = $user->email;
        $displayType = strtoupper($user->user_type ?? 'ADMIN');
    }
@endphp

<div class="dropdown nxl-h-item">
    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
        <img src="{{ $avatar }}" 
             alt="{{ $displayName }}" 
             class="img-fluid user-avtar me-0" 
             style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
             onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&color=7F9CF5&background=EBF4FF&size=128&bold=true';" />
    </a>
    
    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
        <div class="dropdown-header">
            <div class="d-flex align-items-center">
                <img src="{{ $avatar }}" 
                     alt="{{ $displayName }}" 
                     class="img-fluid user-avtar" 
                     style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"
                     onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name={{ urlencode($displayName) }}&color=7F9CF5&background=EBF4FF&size=128&bold=true';" />
                <div class="ms-2">
                    <h6 class="text-dark mb-0">
                        {{ $displayName }}
                        <span class="badge bg-soft-success text-success ms-1">
                            {{ $displayType }}
                        </span>
                    </h6>
                    <span class="fs-12 fw-medium text-muted">{{ $displayEmail }}</span>
                </div>
            </div>
        </div>
        
        <div class="dropdown-divider"></div>
        
        <a href="" class="dropdown-item">
            <i class="feather-user"></i>
            <span>Profile Details</span>
        </a>
        
        <a href="" class="dropdown-item">
            <i class="feather-settings"></i>
            <span>Account Settings</span>
        </a>
        
        <div class="dropdown-divider"></div>
        
        <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="dropdown-item">
            <i class="feather-log-out"></i>
            <span>Logout</span>
        </a>
    </div>
</div>
            <!--! [End] Header Right !-->
        </div>
    </header>
    <!--! ================================================================ !-->
    <!--! [End] Header !-->
    <!--! ================================================================ !-->
    
    <!--! ================================================================ !-->
    <!--! [Start] Main Content !-->
    <!--! ================================================================ !-->
    
    <!-- Main content area with independent scrolling -->
    <div class="main-content">
        @yield('content')
    </div>
    <!--! ================================================================ !-->
    <!--! [End] Main Content !-->
    <!--! ================================================================ !-->

    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
    <!-- Vendors JS (MUST be on top) -->
    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/js/daterangepicker.min.js') }}"></script>

    <!-- Check if this is dashboard page -->
    @php
        $isDashboardPage = request()->routeIs('hod.dashboard');
    @endphp

    @if($isDashboardPage)
        <!-- Only load chart libraries on dashboard page -->
        <script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
        <script src="{{ asset('assets/vendors/js/circle-progress.min.js') }}"></script>
        <script src="{{ asset('assets/js/dashboard-init.min.js') }}"></script>
    @endif

    <!-- App Init -->
    <script src="{{ asset('assets/js/common-init.min.js') }}"></script>

    <!-- Theme Customizer -->
    <script src="{{ asset('assets/js/theme-customizer-init.min.js') }}"></script>

    <!-- Custom Mobile Menu Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileCollapseBtn = document.getElementById('mobile-collapse');
            const sidebar = document.querySelector('.nxl-navigation');
            const mobileOverlay = document.getElementById('mobileOverlay');
            const menuMiniButton = document.getElementById('menu-mini-button');
            const menuExpendButton = document.getElementById('menu-expend-button');
            const body = document.body;
            const header = document.querySelector('.nxl-header');
            
            // Mobile menu functionality
            if (mobileCollapseBtn && sidebar && mobileOverlay) {
                mobileCollapseBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    sidebar.classList.toggle('mobile-open');
                    mobileOverlay.classList.toggle('active');
                    body.classList.toggle('mobile-menu-open');
                    
                    if (sidebar.classList.contains('mobile-open')) {
                        body.style.overflow = 'hidden';
                    } else {
                        body.style.overflow = '';
                    }
                });
                
                mobileOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('mobile-open');
                    mobileOverlay.classList.remove('active');
                    body.classList.remove('mobile-menu-open');
                    body.style.overflow = '';
                });
            }
            
            // Desktop collapse/expand functionality
            if (menuMiniButton && menuExpendButton && sidebar && header) {
                menuMiniButton.addEventListener('click', function() {
                    sidebar.classList.add('collapsed');
                    body.classList.add('sidebar-collapsed');
                    menuMiniButton.style.display = 'none';
                    menuExpendButton.style.display = 'block';
                    
                    if (window.innerWidth >= 992) {
                        header.style.left = '70px';
                    }
                });
                
                menuExpendButton.addEventListener('click', function() {
                    sidebar.classList.remove('collapsed');
                    body.classList.remove('sidebar-collapsed');
                    menuExpendButton.style.display = 'none';
                    menuMiniButton.style.display = 'block';
                    
                    if (window.innerWidth >= 992) {
                        header.style.left = '280px';
                    }
                });
                
                function checkScreenSize() {
                    if (window.innerWidth < 992) {
                        menuMiniButton.style.display = 'none';
                        menuExpendButton.style.display = 'none';
                        sidebar.classList.remove('collapsed');
                        body.classList.remove('sidebar-collapsed');
                        header.style.left = '0';
                    } else {
                        if (sidebar.classList.contains('collapsed')) {
                            menuMiniButton.style.display = 'none';
                            menuExpendButton.style.display = 'block';
                            header.style.left = '70px';
                        } else {
                            menuMiniButton.style.display = 'block';
                            menuExpendButton.style.display = 'none';
                            header.style.left = '280px';
                        }
                    }
                }
                
                checkScreenSize();
                window.addEventListener('resize', checkScreenSize);
            }
            
            // Handle submenu toggling on mobile
            const menuItems = document.querySelectorAll('.nxl-hasmenu > .nxl-link');
            menuItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    if (window.innerWidth < 992) {
                        e.preventDefault();
                        const parent = this.parentElement;
                        parent.classList.toggle('active');
                    }
                });
            });
            
            // Set active menu item based on current URL
            const currentPath = window.location.pathname;
            const menuItemsAll = document.querySelectorAll('.hod-menu-item');
            
            menuItemsAll.forEach(item => {
                item.classList.remove('active');
            });
            
            menuItemsAll.forEach(item => {
                const link = item.querySelector('.nxl-link');
                if (link) {
                    const href = link.getAttribute('href');
                    
                    if (href === '#' && currentPath.includes('/hod/dashboard')) {
                        item.classList.add('active');
                    } else if (href && href !== 'javascript:void(0);' && currentPath.includes(href)) {
                        item.classList.add('active');
                    }
                }
            });
            
            menuItemsAll.forEach(item => {
                const submenuLinks = item.querySelectorAll('.nxl-submenu .nxl-link');
                let hasActiveChild = false;
                
                submenuLinks.forEach(subLink => {
                    const subHref = subLink.getAttribute('href');
                    if (subHref && subHref !== 'javascript:void(0);' && currentPath.includes(subHref)) {
                        hasActiveChild = true;
                    }
                });
                
                if (hasActiveChild) {
                    item.classList.add('active');
                }
            });
        });
        
        // Prevent ApexCharts errors if loaded on non-dashboard pages
        if (typeof ApexCharts !== 'undefined') {
            const chartElements = [
                'enrollmentChart',
                'performanceChart',
                'financialChart'
            ];
            
            let hasChartElement = false;
            chartElements.forEach(id => {
                if (document.getElementById(id)) {
                    hasChartElement = true;
                }
            });
            
            if (!hasChartElement) {
                const originalInit = ApexCharts.prototype.init;
                ApexCharts.prototype.init = function() {
                    if (this.el && document.body.contains(this.el)) {
                        return originalInit.apply(this, arguments);
                    }
                    return null;
                };
            }
        }
    </script>

    @stack('scripts')
</body>

</html>