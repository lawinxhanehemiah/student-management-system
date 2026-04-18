@props(['stats' => []])

<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="d-block mb-2 fw-medium">Total Receivable</span>
                        <h3 class="fw-semibold mb-2">{{ number_format($stats['total_receivable'] ?? 0, 2) }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success-transparent">
                                <i class="feather-arrow-up me-1"></i>
                                {{ $stats['current_percentage'] ?? 0 }}% Current
                            </span>
                        </div>
                    </div>
                    <div class="avatar avatar-lg bg-primary-transparent">
                        <i class="feather-credit-card fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="d-block mb-2 fw-medium">Overdue Amount</span>
                        <h3 class="fw-semibold mb-2 text-danger">{{ number_format($stats['overdue_amount'] ?? 0, 2) }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning-transparent">
                                <i class="feather-clock me-1"></i>
                                {{ $stats['overdue_percentage'] ?? 0 }}% of Total
                            </span>
                        </div>
                    </div>
                    <div class="avatar avatar-lg bg-danger-transparent">
                        <i class="feather-alert-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="d-block mb-2 fw-medium">Collection Rate</span>
                        <h3 class="fw-semibold mb-2">{{ $stats['collection_rate'] ?? 0 }}%</h3>
                        <div class="progress progress-sm" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: {{ $stats['collection_rate'] ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div class="avatar avatar-lg bg-success-transparent">
                        <i class="feather-trending-up fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-lg-6 col-md-6">
        <div class="card custom-card">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    <div class="flex-fill">
                        <span class="d-block mb-2 fw-medium">Avg. Days Overdue</span>
                        <h3 class="fw-semibold mb-2">{{ $stats['avg_days_overdue'] ?? 0 }}</h3>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info-transparent">
                                <i class="feather-calendar me-1"></i>
                                {{ $stats['total_overdue_count'] ?? 0 }} invoices
                            </span>
                        </div>
                    </div>
                    <div class="avatar avatar-lg bg-info-transparent">
                        <i class="feather-clock fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>