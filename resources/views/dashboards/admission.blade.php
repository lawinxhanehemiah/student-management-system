@extends('layouts.admission')

@section('title', 'Admission Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-2 text-dark">Admission Dashboard Overview</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
            <div>
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary" id="refreshDashboard">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards -->
    <div class="row g-2 mb-3">
        <!-- Total Applications -->
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-primary">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title mb-1">TOTAL APPLICATIONS</div>
                            <div class="stat-number">{{ number_format($totalApplications) }}</div>
                            <small class="text-muted">
                                This Month: {{ number_format($monthlyApplications) }}
                            </small>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Review -->
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-warning">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title mb-1">PENDING REVIEW</div>
                            <div class="stat-number">{{ number_format($pendingReview) }}</div>
                            <small class="text-muted">
                                Awaiting admission officer
                            </small>
                        </div>
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approved Applications -->
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-success">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title mb-1">APPROVED</div>
                            <div class="stat-number">{{ number_format($approvedApplications) }}</div>
                            <small class="text-muted">
                                Ready for enrollment
                            </small>
                        </div>
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejected Applications -->
        <div class="col-xl-3 col-md-6">
            <div class="card dashboard-stat-card border-start border-4 border-danger">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="stat-title mb-1">REJECTED</div>
                            <div class="stat-number">{{ number_format($rejectedApplications) }}</div>
                            <small class="text-muted">
                                This academic year
                            </small>
                        </div>
                        <div class="stat-icon bg-danger bg-opacity-10 text-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4">
        <!-- Application Trends -->
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Application Trends</h5>
                </div>
                <div class="card-body">
                    <canvas id="applicationChart" height="250"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- New Application -->
                        <div class="col-md-6">
                            <a href="{{ route('admission.applications.create') }}"
                               class="card action-card text-center p-4 h-100">
                                <div class="action-icon bg-primary bg-opacity-10 text-primary rounded-circle mx-auto mb-3">
                                    <i class="fas fa-plus-circle fs-3"></i>
                                </div>
                                <h6 class="mb-1">New Application</h6>
                                <p class="text-muted small mb-0">Create new applicant record</p>
                            </a>
                        </div>

                        <!-- Review Applications -->
                        <div class="col-md-6">
                            <a href="#"
                               class="card action-card text-center p-4 h-100">
                                <div class="action-icon bg-warning bg-opacity-10 text-warning rounded-circle mx-auto mb-3">
                                    <i class="fas fa-clipboard-check fs-3"></i>
                                </div>
                                <h6 class="mb-1">Review Applications</h6>
                                <p class="text-muted small mb-0">Process pending applications</p>
                            </a>
                        </div>

                        <!-- Generate Admission Letter -->
                        <div class="col-md-6">
                            <a href="#"
                               class="card action-card text-center p-4 h-100">
                                <div class="action-icon bg-success bg-opacity-10 text-success rounded-circle mx-auto mb-3">
                                    <i class="fas fa-file-certificate fs-3"></i>
                                </div>
                                <h6 class="mb-1">Admission Letters</h6>
                                <p class="text-muted small mb-0">Generate admission letters</p>
                            </a>
                        </div>

                        <!-- Reports -->
                        <div class="col-md-6">
                            <a href="#"
                               class="card action-card text-center p-4 h-100">
                                <div class="action-icon bg-info bg-opacity-10 text-info rounded-circle mx-auto mb-3">
                                    <i class="fas fa-chart-pie fs-3"></i>
                                </div>
                                <h6 class="mb-1">Admission Reports</h6>
                                <p class="text-muted small mb-0">View detailed analytics</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Refresh Dashboard
    $('#refreshDashboard').click(function() {
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Refreshing...');
        location.reload();
    });
    
    // Application Chart
    const applicationCtx = document.getElementById('applicationChart').getContext('2d');
    const applicationChart = new Chart(applicationCtx, {
        type: 'line',
        data: {
            labels: @json($applicationMonths),
            datasets: [{
                label: 'Applications Received',
                data: @json($applicationData),
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Applications Processed',
                data: @json($processedData),
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
                        text: 'Number of Applications'
                    }
                }
            }
        }
    });
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

.dashboard-stat-card {
    transition: transform 0.2s;
}

.dashboard-stat-card:hover {
    transform: translateY(-2px);
}

.stat-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #6c757d;
}

.stat-number {
    font-size: 1.75rem;
    font-weight: 700;
    color: #343a40;
    line-height: 1.2;
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
</style>
@endpush
@endsection