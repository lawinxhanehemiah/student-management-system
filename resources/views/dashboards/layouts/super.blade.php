<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Super Admin Dashboard | MHCS Information System</title>
  
  <!-- Bootstrap 5 + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
    :root {
      --sidebar-width: 250px;
      --sidebar-collapsed-width: 80px;
      --primary: #2c5282;
      --primary-dark: #1a365d;
      --light-bg: #f8f9fc;
      --dark-bg: #121826;
      --dark-card: #1e293b;
      --dark-text: #e2e8f0;
      --dark-sidebar: #1a202c;
      --transition: all 0.35s ease;
      --top-navbar-height: 60px;
    }

    [data-theme="dark"] {
      --light-bg: #121826;
      --dark-text: #e2e8f0;
    }

    body {
      background: var(--light-bg);
      color: var(--dark-text);
      font-family: system-ui, -apple-system, sans-serif;
      overflow-x: hidden;
      transition: var(--transition);
      min-height: 100vh;
    }

    /* ===== SIDEBAR ===== */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      width: var(--sidebar-width);
      height: 100vh;
      background: linear-gradient(180deg, #2c5282 0%, #1a365d 100%);
      color: white;
      transition: var(--transition);
      z-index: 1050;
      overflow-y: auto;
      overflow-x: hidden;
    }

    [data-theme="dark"] .sidebar {
      background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
    }

    .sidebar.collapsed {
      width: var(--sidebar-collapsed-width);
    }

    .sidebar.collapsed .sidebar-text,
    .sidebar.collapsed .submenu,
    .sidebar.collapsed .brand-full,
    .sidebar.collapsed .university-logo {
      display: none;
    }

    .sidebar.collapsed .brand-short {
      display: block !important;
    }

    .sidebar-header {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid rgba(255,255,255,0.12);
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: var(--top-navbar-height);
      min-height: var(--top-navbar-height);
    }

    .brand-area {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    /* LOGO STYLES - IMPROVED */
    .university-logo {
      width: 40px;
      height: 40px;
      background: white;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      flex-shrink: 0;
    }

    .university-logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      border-radius: 6px; /* Slightly smaller radius than container */
    }

    /* Alternative for text-only logo */
    .logo-text {
      color: #2c5282;
      font-weight: bold;
      font-size: 1.1rem;
    }

    .brand-full {
      font-size: 1.2rem;
      font-weight: 700;
      line-height: 1.2;
      white-space: nowrap;
    }

    .brand-full small {
      font-size: 0.75rem;
      opacity: 0.9;
      display: block;
      font-weight: normal;
    }

    .brand-short {
      font-size: 1.4rem;
      font-weight: bold;
      display: none;
    }

    .toggle-btn {
      background: rgba(255,255,255,0.15);
      border: none;
      color: white;
      width: 32px;
      height: 32px;
      border-radius: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      flex-shrink: 0;
    }

    .toggle-btn:hover {
      background: rgba(255,255,255,0.25);
    }

    .sidebar-content {
      height: calc(100vh - var(--top-navbar-height));
      overflow-y: auto;
      padding: 1rem 0;
    }

    .sidebar-content::-webkit-scrollbar {
      width: 4px;
    }

    .sidebar-content::-webkit-scrollbar-track {
      background: rgba(255,255,255,0.1);
    }

    .sidebar-content::-webkit-scrollbar-thumb {
      background: rgba(255,255,255,0.3);
      border-radius: 2px;
    }

    /* Navigation Links with Bright Colored Icons */
    .nav-link {
      color: rgba(255,255,255,0.85);
      padding: 0.75rem 1.25rem;
      border-radius: 0;
      display: flex;
      align-items: center;
      gap: 12px;
      transition: var(--transition);
      white-space: nowrap;
      border-left: 3px solid transparent;
    }

    .nav-link:hover,
    .nav-link.active {
      background: rgba(255,255,255,0.12);
      color: white;
      border-left: 3px solid #ed8936;
    }

    .nav-link.active {
      background: rgba(255,255,255,0.08);
    }

    /* Bright Colored Icons */
    .nav-link i {
      font-size: 1.1rem;
      width: 24px;
      text-align: center;
    }

    /* Dashboard - Cyan */
    .nav-link i.fa-tachometer-alt,
    .nav-link i.fa-gauge-high {
      color: #0ea5e9;
      text-shadow: 0 0 10px rgba(14, 165, 233, 0.5);
    }

    /* Users - Emerald */
    .nav-link i.fa-users-cog,
    .nav-link i.fa-user-group,
    .nav-link i.fa-users {
      color: #10b981;
      text-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
    }

    /* System - Amber */
    .nav-link i.fa-cogs,
    .nav-link i.fa-gear,
    .nav-link i.fa-sliders {
      color: #f59e0b;
      text-shadow: 0 0 10px rgba(245, 158, 11, 0.5);
    }

    /* Reports - Violet */
    .nav-link i.fa-chart-line,
    .nav-link i.fa-chart-bar,
    .nav-link i.fa-chart-pie {
      color: #8b5cf6;
      text-shadow: 0 0 10px rgba(139, 92, 246, 0.5);
    }

    /* Security - Rose */
    .nav-link i.fa-shield-alt,
    .nav-link i.fa-shield-halved,
    .nav-link i.fa-lock {
      color: #f43f5e;
      text-shadow: 0 0 10px rgba(244, 63, 94, 0.5);
    }

    /* Add User - Sky */
    .nav-link i.fa-user-plus {
      color: #38bdf8;
      text-shadow: 0 0 10px rgba(56, 189, 248, 0.5);
    }

    /* Super Admin - Yellow */
    .nav-link i.fa-user-shield {
      color: #eab308;
      text-shadow: 0 0 10px rgba(234, 179, 8, 0.5);
    }

    /* Students - Teal */
    .nav-link i.fa-user-graduate {
      color: #14b8a6;
      text-shadow: 0 0 10px rgba(20, 184, 166, 0.5);
    }

    /* Lecturers - Orange */
    .nav-link i.fa-chalkboard-teacher {
      color: #f97316;
      text-shadow: 0 0 10px rgba(249, 115, 22, 0.5);
    }

    /* University - Indigo */
    .nav-link i.fa-university {
      color: #6366f1;
      text-shadow: 0 0 10px rgba(99, 102, 241, 0.5);
    }

    /* Calendar - Lime */
    .nav-link i.fa-calendar-alt {
      color: #84cc16;
      text-shadow: 0 0 10px rgba(132, 204, 22, 0.5);
    }

    /* Book - Cyan */
    .nav-link i.fa-book {
      color: #06b6d4;
      text-shadow: 0 0 10px rgba(6, 182, 212, 0.5);
    }

    /* Money - Green */
    .nav-link i.fa-file-invoice-dollar {
      color: #22c55e;
      text-shadow: 0 0 10px rgba(34, 197, 94, 0.5);
    }

    /* Logout - Red */
    .nav-link i.fa-sign-out-alt,
    .nav-link i.fa-right-from-bracket {
      color: #ef4444;
      text-shadow: 0 0 10px rgba(239, 68, 68, 0.5);
    }

    .submenu {
      background: rgba(0,0,0,0.15);
      padding: 0.4rem 0;
    }

    .submenu .nav-link {
      padding-left: 3.5rem;
      font-size: 0.9rem;
      border-left: none;
    }

    .submenu .nav-link:hover {
      background: rgba(255,255,255,0.1);
      transform: translateX(5px);
    }

    /* ===== MAIN CONTENT ===== */
    .main-content {
      margin-left: var(--sidebar-width);
      transition: var(--transition);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .sidebar.collapsed ~ .main-content {
      margin-left: var(--sidebar-collapsed-width);
    }

    /* Top Navbar with Icons */
    .top-navbar {
      background: white;
      color: #2d3748;
      box-shadow: 0 2px 10px rgba(0,0,0,0.08);
      padding: 0 1.5rem;
      position: sticky;
      top: 0;
      z-index: 1040;
      transition: var(--transition);
      height: var(--top-navbar-height);
      min-height: var(--top-navbar-height);
      display: flex;
      align-items: center;
      justify-content: space-between;
      border-bottom: 1px solid #e2e8f0;
    }

    [data-theme="dark"] .top-navbar {
      background: var(--dark-card);
      color: var(--dark-text);
      box-shadow: 0 2px 10px rgba(0,0,0,0.3);
      border-bottom-color: #4a5568;
    }

    /* Header Icons Container */
    .header-icons {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    /* Icon Buttons in Header */
    .icon-btn {
      background: transparent;
      border: 1px solid #e2e8f0;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      color: #4a5568;
      position: relative;
    }

    [data-theme="dark"] .icon-btn {
      border-color: #4a5568;
      color: #e2e8f0;
    }

    .icon-btn:hover {
      background: #f7fafc;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    

    [data-theme="dark"] .icon-btn:hover {
      background: #2d3748;
    }

    /* Notification Badge */
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background: #e53e3e;
      color: white;
      font-size: 0.7rem;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }

    /* Notification Dropdown */
    .notification-dropdown {
      width: 350px;
      max-height: 400px;
      overflow-y: auto;
    }

    .notification-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 16px;
      border-bottom: 1px solid #e2e8f0;
    }

    [data-theme="dark"] .notification-header {
      border-bottom-color: #4a5568;
    }

    .notification-tabs {
      display: flex;
      border-bottom: 1px solid #e2e8f0;
    }

    [data-theme="dark"] .notification-tabs {
      border-bottom-color: #4a5568;
    }

    .notification-tab {
      flex: 1;
      padding: 10px;
      background: none;
      border: none;
      border-bottom: 2px solid transparent;
      cursor: pointer;
      transition: var(--transition);
    }

    .notification-tab.active {
      border-bottom-color: #3b82f6;
      color: #3b82f6;
      font-weight: 600;
    }

    [data-theme="dark"] .notification-tab.active {
      color: #60a5fa;
      border-bottom-color: #60a5fa;
    }

    .notification-item {
      padding: 12px 16px;
      border-bottom: 1px solid #f0f0f0;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    [data-theme="dark"] .notification-item {
      border-bottom-color: #4a5568;
    }

    .notification-item:hover {
      background-color: #f8f9fa;
    }

    [data-theme="dark"] .notification-item:hover {
      background-color: #374151;
    }

    .notification-item.unread {
      background-color: #f0f9ff;
    }

    [data-theme="dark"] .notification-item.unread {
      background-color: #1e3a8a;
    }

    .notification-title {
      font-weight: 600;
      font-size: 0.9rem;
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .notification-icon {
      width: 20px;
      height: 20px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 0.7rem;
    }

    .notification-icon.message {
      background: #3b82f6;
      color: white;
    }

    .notification-icon.alert {
      background: #f59e0b;
      color: white;
    }

    .notification-icon.system {
      background: #10b981;
      color: white;
    }

    .notification-message {
      font-size: 0.85rem;
      color: #6b7280;
      margin-bottom: 6px;
      line-height: 1.4;
    }

    [data-theme="dark"] .notification-message {
      color: #a0aec0;
    }

    .notification-time {
      font-size: 0.75rem;
      color: #9ca3af;
    }

    /* Profile Dropdown */
    .profile-dropdown {
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
    }

    .profile-img {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #e2e8f0;
    }

    [data-theme="dark"] .profile-img {
      border-color: #4a5568;
    }

    .profile-name {
      font-weight: 500;
      font-size: 0.9rem;
    }

    /* Mobile Menu Button */
    .mobile-toggle-btn {
      display: none;
      background: transparent;
      border: 1px solid #e2e8f0;
      width: 40px;
      height: 40px;
      border-radius: 8px;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      color: #4a5568;
    }

    [data-theme="dark"] .mobile-toggle-btn {
      border-color: #4a5568;
      color: #e2e8f0;
    }

    /* Content Area */
    .content-area {
      flex: 1;
      top: 0;
      padding: 0;
      overflow-y: auto;
      height: calc(100vh - var(--top-navbar-height));
      background: var(--light-bg);
    }

    [data-theme="dark"] .content-area {
      background: var(--dark-bg);
    }

    /* Dashboard Cards */
    .dashboard-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
      transition: var(--transition);
      height: 100%;
      border: 1px solid #e2e8f0;
    }

    [data-theme="dark"] .dashboard-card {
      background: var(--dark-card);
      color: var(--dark-text);
      box-shadow: 0 4px 6px rgba(0,0,0,0.2);
      border-color: #4a5568;
    }

    .card-icon {
      width: 50px;
      height: 50px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
    }

    .stat-number {
      font-size: 1.75rem;
      font-weight: 700;
      line-height: 1;
    }

    .stat-title {
      font-size: 0.85rem;
      color: #6c757d;
      margin-bottom: 0.25rem;
    }

    [data-theme="dark"] .stat-title {
      color: #a0aec0;
    }

    /* Mobile Sidebar */
    .mobile-sidebar {
      position: fixed;
      top: 0;
      left: -100%;
      width: 280px;
      height: 100vh;
      background: linear-gradient(180deg, #2c5282 0%, #1a365d 100%);
      color: white;
      z-index: 1060;
      transition: left 0.3s ease;
      overflow-y: auto;
    }

    [data-theme="dark"] .mobile-sidebar {
      background: linear-gradient(180deg, #2d3748 0%, #1a202c 100%);
    }

    .mobile-sidebar.show {
      left: 0;
    }

    .mobile-sidebar-header {
      padding: 1rem 1.25rem;
      border-bottom: 1px solid rgba(255,255,255,0.12);
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: var(--top-navbar-height);
    }

    .mobile-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      z-index: 1055;
      display: none;
    }

    .mobile-overlay.show {
      display: block;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 992px) {
      .sidebar {
        display: none;
      }
      
      .mobile-toggle-btn {
        display: flex;
      }
      
      .main-content {
        margin-left: 0 !important;
      }
      
      .content-area {
        padding: 1rem;
      }
      
      .top-navbar {
        padding: 0 1rem;
      }
      
      .profile-name {
        display: none;
      }
      
      .notification-dropdown {
        width: 300px;
      }
    }

    @media (max-width: 768px) {
      .stat-number {
        font-size: 1.5rem;
      }
      
      .dashboard-card {
        padding: 1.25rem;
      }
      
      .top-navbar h5 {
        font-size: 1rem;
      }
      
      .notification-dropdown {
        width: 280px;
      }
    }

    @media (max-width: 576px) {
      .content-area {
        padding: 0.75rem;
      }
      
      .top-navbar {
        height: 55px;
        min-height: 55px;
      }
      
      .header-icons {
        gap: 6px;
      }
      
      .icon-btn {
        width: 36px;
        height: 36px;
      }
    }
  </style>
</head>
<body data-theme="light">

<!-- Desktop Sidebar (Visible on > 992px) -->
<nav class="sidebar" id="desktopSidebar">
  <div class="sidebar-header">
    <div class="brand-area">
      <div class="university-logo">
        <!-- Logo with better handling -->
        <img src="{{ asset('images/logo.WEBP') }}" alt="MHCS Logo" 
             onerror="this.onerror=null; this.style.display='none'; 
                      const span = document.createElement('span');
                      span.className = 'logo-text';
                      span.textContent = 'MHCS';
                      span.style.color = '#2c5282';
                      span.style.fontWeight = 'bold';
                      span.style.fontSize = '1rem';
                      this.parentElement.appendChild(span);">
      </div>
      <div>
        <div class="brand-full">
          MHCS<br>
          <small>Information System</small>
        </div>
        <div class="brand-short">M</div>
      </div>
    </div>
    <button class="toggle-btn" id="desktopSidebarToggle">
      <i class="fas fa-bars"></i>
    </button>
  </div>

  <div class="sidebar-content">
    <div class="mt-2">
      <a href="#" class="nav-link active">
        <i class="fas fa-gauge-high fa-fw"></i>
        <span class="sidebar-text">Dashboard</span>
      </a>

      <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#usersMenu">
        <i class="fas fa-user-group fa-fw"></i>
        <span class="sidebar-text">Users Management</span>
        <i class="fas fa-chevron-down ms-auto small"></i>
      </a>
      <div class="collapse submenu" id="usersMenu">
        <a href="{{ route('superadmin.users.create') }}" class="nav-link"><i class="fas fa-user-plus"></i> Add User</a>
        <a href="{{ route('superadmin.users.index') }}?role=SuperAdmin" class="nav-link"><i class="fas fa-user-shield"></i> Super Admins</a>
        <a href="{{ route('superadmin.users.index') }}?role=Student" class="nav-link"><i class="fas fa-user-graduate"></i> Students</a>
        <a href="{{ route('superadmin.users.index') }}?role=Lecturer" class="nav-link"><i class="fas fa-chalkboard-teacher"></i> Lecturers</a>
      </div>


      <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#systemMenu">
        <i class="fas fa-sliders fa-fw"></i>
        <span class="sidebar-text">System Setup</span>
        <i class="fas fa-chevron-down ms-auto small"></i>
      </a>
      <div class="collapse submenu" id="systemMenu">
        <a href="#" class="nav-link"><i class="fas fa-university"></i> Universities</a>
        <a href="#" class="nav-link"><i class="fas fa-calendar-alt"></i> Academic Year</a>
        <a href="#" class="nav-link"><i class="fas fa-book"></i> Courses & Modules</a>
        <a href="#" class="nav-link"><i class="fas fa-file-invoice-dollar"></i> Fees & Payments</a>
      </div>

      <a href="#" class="nav-link">
        <i class="fas fa-chart-pie fa-fw"></i>
        <span class="sidebar-text">Reports & Analytics</span>
      </a>

      <a href="#" class="nav-link">
        <i class="fas fa-shield-halved fa-fw"></i>
        <span class="sidebar-text">Security & Logs</span>
      </a>

      <div class="mt-auto p-3">
        <a href="#" class="nav-link text-danger">
          <i class="fas fa-right-from-bracket fa-fw"></i>
          <span class="sidebar-text">Logout</span>
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- Mobile Overlay -->
<div class="mobile-overlay" id="mobileOverlay"></div>

<!-- Mobile Sidebar (Visible on < 992px) -->
<nav class="mobile-sidebar" id="mobileSidebar">
  <div class="mobile-sidebar-header">
    <div class="brand-area">
      <div class="university-logo">
        <!-- Same logo for mobile -->
        <img src="{{ asset('images/logo.WEBP') }}" alt="MHCS Logo"
             onerror="this.onerror=null; this.style.display='none'; 
                      const span = document.createElement('span');
                      span.className = 'logo-text';
                      span.textContent = 'MHCS';
                      span.style.color = '#2c5282';
                      span.style.fontWeight = 'bold';
                      span.style.fontSize = '1rem';
                      this.parentElement.appendChild(span);">
      </div>
      <div class="brand-full">
        MHCS<br>
        <small>Information System</small>
      </div>
    </div>
    <button class="toggle-btn" id="closeMobileSidebar">
      <i class="fas fa-times"></i>
    </button>
  </div>

  <div class="sidebar-content">
    <div class="mt-2">
      <a href="#" class="nav-link active" onclick="closeMobileSidebar()">
        <i class="fas fa-gauge-high fa-fw"></i>
        <span class="sidebar-text">Dashboard</span>
      </a>

      <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#mobileUsersMenu">
        <i class="fas fa-user-group fa-fw"></i>
        <span class="sidebar-text">Users Management</span>
        <i class="fas fa-chevron-down ms-auto small"></i>
      </a>
      <div class="collapse submenu" id="mobileUsersMenu">
        <a href="#" class="nav-link" onclick="closeMobileSidebar()"><i class="fas fa-user-plus"></i> Add User</a>
        <a href="#" class="nav-link" onclick="closeMobileSidebar()"><i class="fas fa-user-shield"></i> Super Admins</a>
        <a href="#" class="nav-link" onclick="closeMobileSidebar()"><i class="fas fa-user-graduate"></i> Students</a>
        <a href="#" class="nav-link" onclick="closeMobileSidebar()"><i class="fas fa-chalkboard-teacher"></i> Lecturers</a>
      </div>

      <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#mobileSystemMenu">
        <i class="fas fa-sliders fa-fw"></i>
        <span class="sidebar-text">System Setup</span>
        <i class="fas fa-chevron-down ms-auto small"></i>
      </a>
      <div class="collapse submenu" id="mobileSystemMenu">
        <a href="#" class="nav-link" onclick="closeMobileSidebar()"><i class="fas fa-university"></i> Universities</a>
        <a href="#" class="nav-link" onclick="closeMobileSidebar()"><i class="fas fa-calendar-alt"></i> Academic Year</a>
        <a href="#" class="nav-link" onclick="closeMobileSidebar()"><i class="fas fa-book"></i> Courses & Modules</a>
        <a href="#" class="nav-link" onclick="closeMobileSidebar()"><i class="fas fa-file-invoice-dollar"></i> Fees & Payments</a>
      </div>

      <a href="#" class="nav-link" onclick="closeMobileSidebar()">
        <i class="fas fa-chart-pie fa-fw"></i>
        <span class="sidebar-text">Reports & Analytics</span>
      </a>

      <a href="#" class="nav-link" onclick="closeMobileSidebar()">
        <i class="fas fa-shield-halved fa-fw"></i>
        <span class="sidebar-text">Security & Logs</span>
      </a>

      <div class="mt-auto p-3">
        <a href="#" class="nav-link text-danger" onclick="closeMobileSidebar()">
          <i class="fas fa-right-from-bracket fa-fw"></i>
          <span class="sidebar-text">Logout</span>
        </a>
      </div>
    </div>
  </div>
</nav>

<!-- MAIN CONTENT AREA -->
<div class="main-content">
  <!-- Top Navbar with Icons -->
  <nav class="top-navbar">
    <div class="d-flex align-items-center gap-3">
      <button class="mobile-toggle-btn" id="mobileMenuBtn">
        <i class="fas fa-bars"></i>
      </button>
      <h5 class="mb-0 fw-semibold">Super Admin Dashboard</h5>
    </div>

    <div class="header-icons">
      <!-- Dark Mode Toggle -->
      <button class="icon-btn" id="themeToggle" title="Toggle dark mode">
        <i class="fas fa-moon" id="themeIcon"></i>
      </button>
      
      <!-- Notifications Dropdown -->
      <div class="dropdown">
        <button class="icon-btn" type="button" data-bs-toggle="dropdown" id="notificationBtn">
          <i class="fas fa-bell"></i>
          <span class="notification-badge" id="notificationCount">5</span>
        </button>
        <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationBtn">
          <div class="notification-header">
            <h6 class="mb-0 fw-bold">Notifications</h6>
            <small class="text-muted" id="notificationSummary">5 unread</small>
          </div>
          
          <div class="notification-tabs">
            <button class="notification-tab active" data-tab="all">All</button>
            <button class="notification-tab" data-tab="messages">Messages</button>
            <button class="notification-tab" data-tab="alerts">Alerts</button>
          </div>
          
          <div class="py-2" id="notificationContent">
            <div class="notification-item unread" data-type="message">
              <div class="notification-title">
                <span class="notification-icon message">
                  <i class="fas fa-envelope"></i>
                </span>
                New Message from Prof. Johnson
              </div>
              <div class="notification-message">
                "Can we schedule a meeting to discuss the new curriculum?"
              </div>
              <div class="notification-time">Just now</div>
            </div>
            
            <div class="notification-item unread" data-type="alert">
              <div class="notification-title">
                <span class="notification-icon alert">
                  <i class="fas fa-exclamation-triangle"></i>
                </span>
                System Alert: Server Load High
              </div>
              <div class="notification-message">
                Server CPU usage is at 85%. Consider scaling resources.
              </div>
              <div class="notification-time">15 minutes ago</div>
            </div>
            
            <div class="notification-item unread" data-type="message">
              <div class="notification-title">
                <span class="notification-icon message">
                  <i class="fas fa-envelope"></i>
                </span>
                Student Registration Complete
              </div>
              <div class="notification-message">
                Sarah Miller has successfully registered for Computer Science.
              </div>
              <div class="notification-time">1 hour ago</div>
            </div>
            
            <div class="notification-item" data-type="system">
              <div class="notification-title">
                <span class="notification-icon system">
                  <i class="fas fa-cogs"></i>
                </span>
                System Update Completed
              </div>
              <div class="notification-message">
                Version 2.3.1 has been successfully deployed.
              </div>
              <div class="notification-time">Yesterday</div>
            </div>
            
            <div class="notification-item" data-type="message">
              <div class="notification-title">
                <span class="notification-icon message">
                  <i class="fas fa-envelope"></i>
                </span>
                Payment Received
              </div>
              <div class="notification-message">
                Tuition payment confirmed for 15 students.
              </div>
              <div class="notification-time">2 days ago</div>
            </div>
          </div>
          
          <div class="px-3 py-2 border-top">
            <a href="#" class="btn btn-sm btn-outline-primary w-100">
              <i class="fas fa-list me-1"></i> View All Notifications
            </a>
          </div>
        </div>
      </div>
      
      <!-- Profile Dropdown -->
      <div class="dropdown">
        <div class="profile-dropdown" data-bs-toggle="dropdown">
          <img src="https://ui-avatars.com/api/?name=Super+Admin&background=2c5282&color=fff&size=128" 
               class="profile-img" alt="Super Admin">
        </div>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> My Profile</a></li>
          <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Account Settings</a></li>
          <li><a class="dropdown-item" href="#"><i class="fas fa-bell me-2"></i> Notification Settings</a></li>
          <li><hr class="dropdown-divider"/></li>
          <li><a class="dropdown-item text-danger" href="#"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Scrollable Content Area -->
   <div class="content-area">
    <!-- Dynamically load content here -->
    @yield('content')
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Desktop Sidebar Toggle
  document.getElementById('desktopSidebarToggle').addEventListener('click', function() {
    document.getElementById('desktopSidebar').classList.toggle('collapsed');
  });

  // Mobile Menu Toggle
  document.getElementById('mobileMenuBtn').addEventListener('click', function() {
    document.getElementById('mobileSidebar').classList.add('show');
    document.getElementById('mobileOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
  });

  // Close Mobile Sidebar
  document.getElementById('closeMobileSidebar').addEventListener('click', closeMobileSidebar);
  document.getElementById('mobileOverlay').addEventListener('click', closeMobileSidebar);

  function closeMobileSidebar() {
    document.getElementById('mobileSidebar').classList.remove('show');
    document.getElementById('mobileOverlay').classList.remove('show');
    document.body.style.overflow = 'auto';
  }

  // Dark Mode Toggle (Works Everywhere)
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');
  
  themeToggle.addEventListener('click', function() {
    const currentTheme = document.body.getAttribute('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    
    document.body.setAttribute('data-theme', newTheme);
    
    // Change icon
    if (newTheme === 'dark') {
      themeIcon.classList.remove('fa-moon');
      themeIcon.classList.add('fa-sun');
      themeToggle.title = 'Switch to light mode';
    } else {
      themeIcon.classList.remove('fa-sun');
      themeIcon.classList.add('fa-moon');
      themeToggle.title = 'Switch to dark mode';
    }
    
    // Save preference to localStorage
    localStorage.setItem('theme', newTheme);
    
    // Update sidebar theme for mobile
    updateSidebarTheme();
  });

  // Update sidebar theme
  function updateSidebarTheme() {
    const theme = document.body.getAttribute('data-theme');
    const mobileSidebar = document.getElementById('mobileSidebar');
    
    if (theme === 'dark') {
      if (mobileSidebar) {
        mobileSidebar.style.background = 'linear-gradient(180deg, #2d3748 0%, #1a202c 100%)';
      }
    } else {
      if (mobileSidebar) {
        mobileSidebar.style.background = 'linear-gradient(180deg, #2c5282 0%, #1a365d 100%)';
      }
    }
  }

  // Load saved theme on page load
  document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.body.setAttribute('data-theme', savedTheme);
    
    if (savedTheme === 'dark') {
      themeIcon.classList.remove('fa-moon');
      themeIcon.classList.add('fa-sun');
      themeToggle.title = 'Switch to light mode';
    }
    
    updateSidebarTheme();
    
    // Initialize notification counts
    updateNotificationCounts();
  });

  // Notification Tabs
  document.querySelectorAll('.notification-tab').forEach(tab => {
    tab.addEventListener('click', function() {
      const tabName = this.getAttribute('data-tab');
      
      // Update active tab
      document.querySelectorAll('.notification-tab').forEach(t => {
        t.classList.remove('active');
      });
      this.classList.add('active');
      
      // Filter notifications
      filterNotifications(tabName);
    });
  });

  function filterNotifications(tab) {
    const notifications = document.querySelectorAll('.notification-item');
    
    notifications.forEach(notification => {
      if (tab === 'all') {
        notification.style.display = 'block';
      } else {
        const type = notification.getAttribute('data-type');
        if (type === tab) {
          notification.style.display = 'block';
        } else {
          notification.style.display = 'none';
        }
      }
    });
  }

  // Notification handling
  document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('click', function() {
      if (this.classList.contains('unread')) {
        this.classList.remove('unread');
        updateNotificationCounts();
      }
    });
  });

  function updateNotificationCounts() {
    // Update notification count
    const notificationItems = document.querySelectorAll('#notificationContent .notification-item.unread');
    const notificationBadge = document.getElementById('notificationCount');
    const notificationSummary = document.getElementById('notificationSummary');
    
    const notificationCount = notificationItems.length;
    notificationBadge.textContent = notificationCount;
    notificationSummary.textContent = notificationCount + ' unread';
    
    if (notificationCount === 0) {
      notificationBadge.style.display = 'none';
    } else {
      notificationBadge.style.display = 'flex';
    }
  }

  // Mark all notifications as read
  document.querySelectorAll('.notification-item').forEach(item => {
    item.addEventListener('dblclick', function() {
      this.classList.remove('unread');
      updateNotificationCounts();
    });
  });

  // Initialize notification count
  updateNotificationCounts();

  // Close mobile sidebar when clicking links
  document.querySelectorAll('#mobileSidebar .nav-link').forEach(link => {
    link.addEventListener('click', closeMobileSidebar);
  });

  // Handle window resize
  window.addEventListener('resize', function() {
    if (window.innerWidth >= 992) {
      closeMobileSidebar();
    }
  });
</script>
</body>
</html>