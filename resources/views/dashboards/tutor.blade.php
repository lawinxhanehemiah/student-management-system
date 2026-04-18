{{-- resources/views/tutor/dashboard.blade.php --}}
@extends('layouts.tutor')

@section('title', 'Tutor Dashboard')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm overflow-hidden rounded-3">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-8 p-4 bg-white">
                            <h2 class="fw-bold text-dark mb-1">Hello, {{ $user->first_name ?? $user->name }}! 👋</h2>
                            <p class="text-muted mb-4">It's a great day to inspire your students. Here is your overview.</p>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="d-flex align-items-center bg-light px-3 py-2 rounded-3">
                                    <i class="feather-calendar text-primary me-2"></i>
                                    <span class="small fw-semibold text-dark">{{ now()->format('l, M j, Y') }}</span>
                                </div>
                                <div class="d-flex align-items-center bg-light px-3 py-2 rounded-3">
                                    <i class="feather-clock text-info me-2"></i>
                                    <span class="small fw-semibold text-dark">Current Term: 2026 Spring</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 d-none d-md-flex align-items-center justify-content-center" 
                             style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 160px;">
                            <div class="text-center text-white p-3">
                                <i class="feather-award mb-2" style="font-size: 3rem; opacity: 0.9;"></i>
                                <p class="mb-0 small fw-light">Tutor Excellence Portal</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="row mb-4">
        @php
            $stats = [
                ['label' => 'Total Courses', 'value' => $totalCourses ?? 0, 'icon' => 'book-open', 'color' => 'primary', 'sub' => 'Active classes'],
                ['label' => 'Total Students', 'value' => $totalStudents ?? 0, 'icon' => 'users', 'color' => 'info', 'sub' => 'Enrolled students'],
                ['label' => 'Pending Grades', 'value' => $pendingAssessments ?? 0, 'icon' => 'edit-3', 'color' => 'warning', 'sub' => 'Action required'],
            ];
        @endphp

        @foreach($stats as $stat)
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm h-100 transition-up">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="bg-{{ $stat['color'] }} bg-opacity-10 p-3 rounded-circle">
                            <i class="feather-{{ $stat['icon'] }} text-{{ $stat['color'] }} fs-4"></i>
                        </div>
                        <div class="text-end">
                            <h3 class="fw-bold mb-0">{{ $stat['value'] }}</h3>
                            <span class="text-muted small">{{ $stat['label'] }}</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top">
                        <small class="text-{{ $stat['color'] }} fw-medium">
                            <i class="feather-activity me-1"></i> {{ $stat['sub'] }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="row mb-4">
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0">Student Performance Trend</h5>
                        <p class="text-muted small mb-0">Average class scores over the last 7 days</p>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border" type="button">Weekly View</button>
                    </div>
                </div>
                <div class="card-body px-4">
                    <canvas id="weeklyChart" style="max-height: 320px;"></canvas>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-0 pt-4 px-4">
                    <h5 class="fw-bold mb-0">Attendance Mix</h5>
                    <p class="text-muted small mb-0">Current month distribution</p>
                </div>
                <div class="card-body d-flex flex-column justify-content-center">
                    <div class="position-relative mb-4">
                        <canvas id="attendanceChart" height="220"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h4 class="fw-bold mb-0"></h4>
                            <span class="text-muted x-small">Overall</span>
                        </div>
                    </div>
                    <div class="row g-2 mt-auto">
                        <div class="col-4 text-center">
                            <div class="p-2 rounded bg-light">
                                <div class="text-success fw-bold">{{ $attendanceStats['present'] ?? 0 }}</div>
                                <div class="x-small text-muted text-uppercase">Present</div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="p-2 rounded bg-light">
                                <div class="text-warning fw-bold">{{ $attendanceStats['late'] ?? 0 }}</div>
                                <div class="x-small text-muted text-uppercase">Late</div>
                            </div>
                        </div>
                        <div class="col-4 text-center">
                            <div class="p-2 rounded bg-light">
                                <div class="text-danger fw-bold">{{ $attendanceStats['absent'] ?? 0 }}</div>
                                <div class="x-small text-muted text-uppercase">Absent</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Active Course Performance Table --}}
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Active Course Performance</h5>
                    <a href="{{ route('tutor.courses.index') }}" class="btn btn-link btn-sm text-decoration-none">Manage All</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Course</th>
                                <th>Students</th>
                                <th>Avg. Score</th>
                                <th class="text-end pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($coursePerformance as $course)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary bg-opacity-10 p-2 rounded me-3 d-none d-sm-block">
                                            <i class="feather-book text-primary"></i>
                                        </div>
                                        <span class="fw-semibold text-dark">{{ $course['name'] }}</span>
                                    </div>
                                </td>
                                <td>{{ $course['students'] ?? 0 }}</td>
                                <td>
                                    <div class="d-flex align-items-center" style="min-width: 120px;">
                                        <span class="me-2 fw-bold small">{{ $course['average'] ?? 0 }}%</span>
                                        <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px;">
                                            <div class="progress-bar bg-{{ ($course['average'] ?? 0) >= 70 ? 'success' : (($course['average'] ?? 0) >= 50 ? 'warning' : 'danger') }}" 
                                                 role="progressbar" style="width: {{ $course['average'] ?? 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    @php $avg = $course['average'] ?? 0; @endphp
                                    @if($avg >= 70)
                                        <span class="badge rounded-pill bg-soft-success text-success px-3">Excellent</span>
                                    @elseif($avg >= 50)
                                        <span class="badge rounded-pill bg-soft-warning text-warning px-3">Good</span>
                                    @else
                                        <span class="badge rounded-pill bg-soft-danger text-danger px-3">Review</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <img src="https://illustrations.popsy.co/gray/data-report.svg" alt="No data" style="height: 80px;" class="mb-3">
                                    <p class="mb-0">No active courses assigned yet.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        {{-- Recent Activity & Toolkit --}}
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">Recent Activity</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="timeline-small">
                        @forelse($recentActivities as $activity)
                        <div class="d-flex mb-4">
                            <div class="flex-shrink-0">
                                <span class="btn btn-sm btn-{{ $activity['type'] ?? 'secondary' }} btn-icon rounded-circle p-1" aria-label="{{ $activity['message'] }}">
                                    <i class="feather-{{ $activity['icon'] ?? 'info' }} small"></i>
                                </span>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <p class="mb-0 text-dark small">{{ $activity['message'] }}</p>
                                <span class="text-muted x-small"><i class="feather-clock me-1"></i>{{ $activity['time'] }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-3">
                            <p class="text-muted small mb-0">Nothing to report yet.</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="card border-0 bg-dark shadow-sm">
                <div class="card-body">
                    <h6 class="text-white fw-bold mb-3">Tutor Toolkit</h6>
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="{{ route('tutor.materials.index') }}" class="btn btn-outline-light w-100 py-3 btn-sm shadow-none" aria-label="Materials">
                                <i class="feather-upload-cloud d-block mb-1"></i> Materials
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('tutor.attendance.index') }}" class="btn btn-outline-light w-100 py-3 btn-sm shadow-none" aria-label="Attendance">
                                <i class="feather-user-check d-block mb-1"></i> Attendance
                            </a>
                        </div>
                        <div class="col-12">
                            <a href="#" class="btn btn-primary w-100 py-2 fw-bold shadow-none" aria-label="New Assessment Result">
                                <i class="feather-plus me-2"></i>New Assessment Result
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-soft-success { background-color: rgba(16, 185, 129, 0.1); }
    .bg-soft-warning { background-color: rgba(245, 158, 11, 0.1); }
    .bg-soft-danger { background-color: rgba(239, 68, 68, 0.1); }
    .x-small { font-size: 0.75rem; }
    .transition-up { transition: transform 0.2s ease-in-out; }
    .transition-up:hover { transform: translateY(-5px); }
</style>

@push('page-scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Weekly Performance Chart
    const weeklyCtx = document.getElementById('weeklyChart')?.getContext('2d');
    if (weeklyCtx) {
        new Chart(weeklyCtx, {
            type: 'line',
            data: {
                labels: @json($weeklyData['days'] ?? []),
                datasets: [{
                    label: 'Avg Score',
                    data: @json($weeklyData['scores'] ?? []),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.05)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, grid: { borderDash: [5, 5] } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Attendance Chart
    const attendanceCtx = document.getElementById('attendanceChart')?.getContext('2d');
    if (attendanceCtx) {
        new Chart(attendanceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Present', 'Late', 'Absent'],
                datasets: [{
                    data: [
                        {{ $attendanceStats['present'] ?? 0 }},
                        {{ $attendanceStats['late'] ?? 0 }},
                        {{ $attendanceStats['absent'] ?? 0 }}
                    ],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    hoverOffset: 4,
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '80%',
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
@endpush
@endsection