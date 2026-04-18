{{-- resources/views/hod/promotion/results.blade.php --}}
@extends('layouts.hod')

@section('title', 'Promotion Results')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-{{ $type == 'semester' ? 'primary' : 'success' }} text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Promotion Results - {{ ucfirst($type) }} Promotion
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Total Processed</h5>
                                    <h2 class="mb-0">{{ $total }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Successfully Promoted</h5>
                                    <h2 class="mb-0">{{ $eligible }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Failed / Ineligible</h5>
                                    <h2 class="mb-0">{{ $ineligible }}</h2>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">Success Rate</h5>
                                    <h2 class="mb-0">
                                        {{ $total > 0 ? round(($eligible / $total) * 100, 1) : 0 }}%
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Successful Promotions -->
                    @if(count($successful) > 0)
                    <div class="card mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-check-circle me-2"></i>
                                Successful Promotions ({{ count($successful) }})
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                         <tr>
                                            <th>Reg No</th>
                                            <th>Student Name</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($successful as $student)
                                        <tr>
                                            <td><strong>{{ $student['reg_no'] }}</strong></td>
                                            <td>{{ $student['name'] }}</td>
                                            <td class="text-success">{{ $student['message'] }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Failed Promotions -->
                    @if(count($failed) > 0)
                    <div class="card">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-times-circle me-2"></i>
                                Failed / Ineligible ({{ count($failed) }})
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Reg No</th>
                                            <th>Student Name</th>
                                            <th>Reason</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($failed as $student)
                                        <tr>
                                            <td><strong>{{ $student['reg_no'] }}</strong></td>
                                            <td>{{ $student['name'] }}</td>
                                            <td class="text-danger">{{ $student['message'] }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-info" 
                                                        onclick="viewConditions({{ json_encode($student['conditions'] ?? []) }})">
                                                    <i class="fas fa-info-circle"></i> Details
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="text-center mt-4">
                        <a href="{{ route('hod.promotion.history') }}" class="btn btn-primary">
                            <i class="fas fa-history"></i> View Promotion History
                        </a>
                        <a href="{{ route('hod.promotion.' . ($type == 'semester' ? 'semester' : 'year')) }}" 
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Promotion
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Conditions Modal -->
<div class="modal fade" id="conditionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">Failed Conditions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul id="conditionsList" class="list-unstyled"></ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewConditions(conditions) {
    var list = $('#conditionsList');
    list.empty();
    
    if (conditions && conditions.length > 0) {
        conditions.forEach(function(condition) {
            list.append('<li class="mb-2"><i class="fas fa-times-circle text-danger me-2"></i> ' + condition + '</li>');
        });
    } else {
        list.append('<li class="text-muted">No specific conditions provided</li>');
    }
    
    $('#conditionsModal').modal('show');
}
</script>
@endpush
@endsection