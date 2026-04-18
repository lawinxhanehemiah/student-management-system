
{{-- resources/views/hod/promotion/history.blade.php --}}
@extends('layouts.hod')

@section('title', 'Promotion History')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Promotion History - {{ $programme->name ?? 'All Programmes' }}
                    </h5>
                    <div class="card-tools">
                        <a href="{{ route('hod.promotion.bulk') }}" class="btn btn-sm btn-success">
                            <i class="fas fa-layer-group"></i> New Bulk Promotion
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="historyTable">
                            <thead class="table-light">
                                 <tr>
                                    <th>Date</th>
                                    <th>Reg No</th>
                                    <th>Student Name</th>
                                    <th>From Level</th>
                                    <th>To Level</th>
                                    <th>From Sem</th>
                                    <th>To Sem</th>
                                    <th>Type</th>
                                    <th>GPA/CGPA</th>
                                    <th>Fee Cleared</th>
                                    <th>Promoted By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $log)
                                <tr>
                                    <td>{{ $log->promoted_at->format('d M Y H:i') }}</td>
                                    <td><strong>{{ $log->student->registration_number ?? 'N/A' }}</strong></td>
                                    <td>
                                        {{ $log->student->user->first_name ?? '' }} 
                                        {{ $log->student->user->last_name ?? '' }}
                                    </td>
                                    <td>Year {{ $log->from_level }}</td>
                                    <td>Year {{ $log->to_level }}</td>
                                    <td>
                                        @if($log->from_semester)
                                            Sem {{ $log->from_semester }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->to_semester)
                                            Sem {{ $log->to_semester }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->promotion_type == 'semester')
                                            <span class="badge bg-info">Semester</span>
                                        @else
                                            <span class="badge bg-success">Level</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->gpa)
                                            <span class="badge bg-{{ $log->gpa >= 2.0 ? 'success' : 'warning' }}">
                                                {{ number_format($log->gpa, 2) }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($log->fee_cleared)
                                            <i class="fas fa-check-circle text-success"></i>
                                        @else
                                            <i class="fas fa-times-circle text-danger"></i>
                                        @endif
                                    </td>
                                    <td>{{ $log->promotedBy->name ?? 'System' }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="11" class="text-center py-5">
                                        <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No promotion history found</p>
                                        <a href="{{ route('hod.promotion.semester') }}" class="btn btn-primary">
                                            Start First Promotion
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-3">
                        {{ $history->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#historyTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ entries"
        }
    });
});
</script>
@endpush
@endsection