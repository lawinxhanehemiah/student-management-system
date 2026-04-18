@extends('layouts.superadmin')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2 text-dark">Dashboard Overview</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
            <div>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" id="refreshDashboard">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" 
                            data-bs-toggle="dropdown">
                        <span class="visually-hidden">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" id="exportDashboard">Export Report</a></li>
                        <li><a class="dropdown-item" href="#" id="printDashboard">Print Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="">Dashboard Settings</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
 <!-- ================= Card ================= -->
 <!-- ================= ROW 1 ================= -->
<div class="row g-2 mb-3">

    <!-- Total Students -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-primary">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">TOTAL STUDENTS</div>
                        <div class="stat-number">{{ number_format($totalStudents) }}</div>
                        <small class="text-muted">
                            Active: 1
                        </small>
                    </div>
                    <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Payments -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-success">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">TOTAL PAYMENTS</div>
                        <div class="stat-number">TZS {{ number_format($totalPayments ?? 0) }}</div>
                        <small class="text-muted">
                            Today: TZS {{ number_format($todayRevenue ?? 0) }}
                        </small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Outstanding Fees -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-danger">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">OUTSTANDING FEES</div>
                        <div class="stat-number">TZS {{ number_format($outstandingFees ?? 0) }}</div>
                        <small class="text-muted">
                            Students unpaid: {{ $studentsWithBalance ?? 0 }}
                        </small>
                    </div>
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Results -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-warning">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">PENDING RESULTS</div>
                        <div class="stat-number">{{ $pendingResults ?? 0 }}</div>
                        <small class="text-muted">
                            Courses not uploaded
                        </small>
                    </div>
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- ================= ROW 2 ================= -->
<div class="row g-2 mb-3">

    <!-- Academic Period -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-info">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">ACADEMIC PERIOD</div>
                        <div class="stat-number">{{ setting('current_academic_year', '-') }}</div>
                        <small class="text-muted">
                            Semester: {{ setting('current_semester', '-') }}
                        </small>
                    </div>
                    <div class="stat-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Courses -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-secondary">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">COURSES</div>
                        <div class="stat-number">{{ $totalCourses ?? 0 }}</div>
                        <small class="text-muted">
                            With results: {{ $coursesWithResults ?? 0 }}
                        </small>
                    </div>
                    <div class="stat-icon bg-secondary bg-opacity-10 text-secondary">
                        <i class="fas fa-book"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Applications -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-dark">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">APPLICATIONS</div>
                        <div class="stat-number">{{ $pendingApplications ?? 0 }}</div>
                        <small class="text-muted">
                            Awaiting approval
                        </small>
                    </div>
                    <div class="stat-icon bg-dark bg-opacity-10 text-dark">
                        <i class="fas fa-file-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Users -->
    <div class="col-xl-3 col-md-6">
        <div class="card dashboard-stat-card border-start border-4 border-success">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stat-title mb-1">SYSTEM USERS</div>
                        <div class="stat-number">{{ $totalUsers ?? 0 }}</div>
                        <small class="text-muted">
                            Admins: {{ $admins ?? 0 }}
                        </small>
                    </div>
                    <div class="stat-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-users-cog"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
 <!-- ================= End Card ================= -->


   <!-- Charts and Detailed Stats -->
<div class="row g-4">

    <!-- Enrollment Trend -->
    <div class="col-xl-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Enrollment Trends</h5>

                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle"
                            type="button"
                            data-bs-toggle="dropdown"
                            aria-expanded="false">
                        Last 6 Months
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" data-period="3">Last 3 Months</a></li>
                        <li><a class="dropdown-item active" href="#" data-period="6">Last 6 Months</a></li>
                        <li><a class="dropdown-item" href="#" data-period="12">Last 12 Months</a></li>
                        <li><a class="dropdown-item" href="#" data-period="24">Last 2 Years</a></li>
                    </ul>
                </div>
            </div>

            <div class="card-body">
                <canvas id="enrollmentChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-xl-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0">Recent Activity</h5>
            </div>

            <div class="card-body p-0">
                <div class="list-group list-group-flush">

                    @forelse($recentActivities as $activity)
                        <div class="list-group-item border-0 py-3">
                            <div class="d-flex align-items-start">

                                <div class="flex-shrink-0">
                                    <div class="avatar-circle-sm bg-{{ $activity->color ?? 'secondary' }} text-white">
                                        <i class="fas fa-{{ $activity->icon ?? 'info-circle' }}"></i>
                                    </div>
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-1">{{ $activity->description }}</p>
                                    <small class="text-muted">{{ $activity->time_ago }}</small>
                                </div>

                                @if(!empty($activity->badge))
                                    <span class="badge bg-{{ $activity->badge_color }}">
                                        {{ $activity->badge }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="fas fa-history text-muted fs-1"></i>
                            <p class="text-muted mt-3 mb-0">No recent activity</p>
                        </div>
                    @endforelse

                </div>
            </div>

            <div class="card-footer text-center">
                <a href="" class="text-primary">
                    View All Activity
                </a>
            </div>
        </div>
    </div>

    <!-- Fee Collection Status -->
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Fee Collection Status</h5>
                <span class="badge bg-primary">Current Semester</span>
            </div>

            <div class="card-body">
                <div class="row align-items-center">

                    <div class="col-md-6">
                        <canvas id="feeCollectionChart" height="200"></canvas>
                    </div>

                    <div class="col-md-6">

                        @php
                            $paid = min(max($feePaidPercentage ?? 0, 0), 100);
                            $pending = min(max($feePendingPercentage ?? 0, 0), 100);
                            $overdue = min(max($feeOverduePercentage ?? 0, 0), 100);
                        @endphp

                        <!-- Paid -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Paid</span>
                                <span>{{ $paid }}%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-success" style="width: {{ $paid }}%"></div>
                            </div>
                        </div>

                        <!-- Pending -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Pending</span>
                                <span>{{ $pending }}%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-warning" style="width: {{ $pending }}%"></div>
                            </div>
                        </div>

                        <!-- Overdue -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Overdue</span>
                                <span>{{ $overdue }}%</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar bg-danger" style="width: {{ $overdue }}%"></div>
                            </div>
                            <small class="text-muted">
                                TZS {{ number_format($feeOverdueAmount ?? 0) }} overdue
                            </small>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Quick Actions -->
<div class="col-xl-6">
    <div class="card h-100">
        <div class="card-header">
            <h5 class="mb-0">Quick Actions</h5>
        </div>

        <div class="card-body">
            <div class="row g-3">

                <!-- Add User -->
                <div class="col-md-6">
                    <a href="{{ route('superadmin.users.create') }}"
                       class="card action-card text-center p-4 h-100">
                        <div class="action-icon bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3">
                            <i class="fas fa-user-plus fs-3"></i>
                        </div>
                        <h6 class="mb-1">Add New User</h6>
                        <p class="text-muted small mb-0">Create staff or student account</p>
                    </a>
                </div>

                <!-- Record Payment -->
                <div class="col-md-6">
                    <a href=""
                       class="card action-card text-center p-4 h-100">
                        <div class="action-icon bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3">
                            <i class="fas fa-money-check-alt fs-3"></i>
                        </div>
                        <h6 class="mb-1">Record Payment</h6>
                        <p class="text-muted small mb-0">Process student fee payments</p>
                    </a>
                </div>

                <!-- Generate Report -->
                <div class="col-md-6">
                    <a href=""
                       class="card action-card text-center p-4 h-100">
                        <div class="action-icon bg-info bg-opacity-10 text-info rounded-circle mx-auto mb-3">
                            <i class="fas fa-chart-bar fs-3"></i>
                        </div>
                        <h6 class="mb-1">Generate Report</h6>
                        <p class="text-muted small mb-0">View academic & financial reports</p>
                    </a>
                </div>

                <!-- Backup -->
                <div class="col-md-6">
                    <a href=""
                       class="card action-card text-center p-4 h-100">
                        <div class="action-icon bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3">
                            <i class="fas fa-database fs-3"></i>
                        </div>
                        <h6 class="mb-1">Backup System</h6>
                        <p class="text-muted small mb-0">Create and manage system backups</p>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>

</div>


        
    
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Refresh Dashboard
    $('#refreshDashboard').click(function() {
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Refreshing...');
        location.reload();
    });
    
    // Export Dashboard
    $('#exportDashboard').click(function(e) {
        e.preventDefault();
        alert('Export feature coming soon!');
    });
    
    // Print Dashboard
    $('#printDashboard').click(function(e) {
        e.preventDefault();
        window.print();
    });
    
    // Enrollment Chart
    const enrollmentCtx = document.getElementById('enrollmentChart').getContext('2d');
    const enrollmentChart = new Chart(enrollmentCtx, {
        type: 'line',
        data: {
            labels: @json($enrollmentMonths),
            datasets: [{
                label: 'New Students',
                data: @json($enrollmentData),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Returning Students',
                data: @json($returningStudentsData),
                borderColor: '#2ecc71',
                backgroundColor: 'rgba(46, 204, 113, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Students'
                    }
                }
            }
        }
    });
    
    // Fee Collection Chart
    const feeCtx = document.getElementById('feeCollectionChart').getContext('2d');
    const feeChart = new Chart(feeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Paid', 'Pending', 'Overdue'],
            datasets: [{
                data: [
                    {{ $feePaidPercentage }},
                    {{ $feePendingPercentage }},
                   
                ],
                backgroundColor: [
                    '#2ecc71',
                    '#f39c12',
                    '#e74c3c'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Period filter for charts
    $('.dropdown-item[data-period]').click(function(e) {
        e.preventDefault();
        const period = $(this).data('period');
        
        // Update active state
        $(this).addClass('active').siblings().removeClass('active');
        $(this).closest('.dropdown').find('.dropdown-toggle').text(`Last ${period} Months`);
        
        // Load new data via AJAX
        $.ajax({
            url: '',
            method: 'POST',
            data: {
                period: period,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                enrollmentChart.data.labels = response.months;
                enrollmentChart.data.datasets[0].data = response.newStudents;
                enrollmentChart.data.datasets[1].data = response.returningStudents;
                enrollmentChart.update();
            }
        });
    });
    
    // Auto refresh dashboard every 2 minutes
    setInterval(function() {
        if (document.visibilityState === 'visible') {
            $.ajax({
                url: '',
                method: 'GET',
                success: function(data) {
                    // Update stats dynamically
                    $('.stat-number').eq(0).text(data.totalStudents.toLocaleString());
                    $('.stat-number').eq(1).text(data.totalStaff.toLocaleString());
                    $('.stat-number').eq(2).text(data.todayRevenue.toLocaleString());
                }
            });
        }
    }, 120000);
});
</script>

<style>
.avatar-circle-sm {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

.action-card {
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
}

.action-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    border-color: #3498db;
}

.action-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.border-purple {
    border-color: #9b59b6 !important;
}

.bg-purple {
    background-color: #9b59b6 !important;
}

.text-purple {
    color: #9b59b6 !important;
}


</style>
@endpush
@endsection