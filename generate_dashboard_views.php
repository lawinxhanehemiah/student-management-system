<?php

$roles = [
    'SuperAdmin',
    'Director',
    'Principal',
    'Deputy_Principal_Academics',
    'Deputy_Principal_Administration',
    'Head_of_Department',
    'Librarian',
    'Examination_Officer',
    'Admission_Officer',
    'ICT_Manager',
    'HR_Manager',
    'Estate_Manager',
    'Records_Officer',
    'Dean_of_Students',
    'PR_Marketing_Officer',
    'Quality_Assurance_Manager',
    'Financial_Controller',
    'Tutor',
    'Student',
    'Secretary',
    'Accountant',
    'Procurement'
];

// Base path
$baseLayoutPath = __DIR__ . '/resources/views/dashboards/layouts/';
$baseViewPath   = __DIR__ . '/resources/views/dashboards/';

foreach ($roles as $role) {

    // layout folder
    $layoutFile = $baseLayoutPath . strtolower($role) . '.blade.php';
    $contentFile = $baseViewPath . strtolower($role) . '/index.blade.php';

    // create folder for view
    $folder = dirname($contentFile);
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
        echo "Created folder: $folder\n";
    }

    // Create layout file if not exists
    if (!file_exists($layoutFile)) {
        $layoutContent = <<<EOD
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$role} Dashboard | MHCS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>

    {{-- Sidebar --}}
    <nav class="sidebar bg-primary text-white p-3">
        <h4>{$role}</h4>
        <ul class="nav flex-column">
            <li class="nav-item"><a href="#" class="nav-link text-white"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        </ul>
    </nav>

    {{-- Top Navbar --}}
    <nav class="navbar navbar-expand navbar-light bg-light px-3">
        <span>Welcome, {{ auth()->user()->name }}</span>
    </nav>

    {{-- Content Area --}}
    <main class="container mt-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
EOD;
        file_put_contents($layoutFile, $layoutContent);
        echo "Created layout: $layoutFile\n";
    }

    // Create blank index.blade.php for the view
    if (!file_exists($contentFile)) {
        $viewContent = <<<EOD
@extends('dashboards.layouts.{$role}')

@section('content')
<div class="container mt-4">
    <h2>{$role} Dashboard</h2>
    <p>Welcome, {{ auth()->user()->name }}!</p>
</div>
@endsection
EOD;
        file_put_contents($contentFile, $viewContent);
        echo "Created view: $contentFile\n";
    }
}

echo "✅ All layouts and index views generated successfully!\n";
