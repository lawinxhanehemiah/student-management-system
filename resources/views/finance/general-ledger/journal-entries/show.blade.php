@extends('layouts.financecontroller')

@section('title', 'Journal Entry Details')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Journal Entry Details</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.general-ledger.journal-entries.index') }}">Journal Entries</a></li>
                <li class="breadcrumb-item active">{{ $entry->journal_number }}</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        @if($entry->status == 'draft')
            <a href="{{ route('finance.general-ledger.journal-entries.edit', $entry->id) }}" 
               class="btn btn-warning">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <button type="button" class="btn btn-success" onclick="postEntry()">
                <i class="fas fa-check me-2"></i>Post
            </button>
            <button type="button" class="btn btn-danger" onclick="cancelEntry()">
                <i class="fas fa-times me-2"></i>Cancel
            </button>
        @endif
        <a href="{{ route('finance.general-ledger.journal-entries.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>
</div>

<!-- Action Forms -->
<form id="post-form" method="POST" action="{{ route('finance.general-ledger.journal-entries.post', $entry->id) }}" style="display: none;">
    @csrf
</form>
<form id="cancel-form" method="POST" action="{{ route('finance.general-ledger.journal-entries.cancel', $entry->id) }}" style="display: none;">
    @csrf
</form>

<div class="row">
    <!-- Journal Info -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5>Journal Information</h5>
            </div>
            <div class="card-body">
                <div class="info-list">
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Journal Number:</span>
                        <span class="value fw-bold">{{ $entry->journal_number }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Entry Date:</span>
                        <span class="value">{{ $entry->entry_date->format('d M Y') }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Type:</span>
                        <span class="value">{!! $entry->type_badge !!}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Status:</span>
                        <span class="value">{!! $entry->status_badge !!}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Total Debit:</span>
                        <span class="value fw-bold">{{ number_format($entry->total_debit, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Total Credit:</span>
                        <span class="value fw-bold">{{ number_format($entry->total_credit, 2) }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Balanced:</span>
                        <span class="value">
                            @if($entry->is_balanced)
                                <span class="badge bg-success">Yes</span>
                            @else
                                <span class="badge bg-danger">No</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created By:</span>
                        <span class="value">{{ $entry->creator->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Created At:</span>
                        <span class="value">{{ $entry->created_at->format('d M Y H:i') }}</span>
                    </div>
                    @if($entry->posted_by)
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Posted By:</span>
                        <span class="value">{{ $entry->poster->name ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item d-flex justify-content-between mb-3">
                        <span class="label">Posted At:</span>
                        <span class="value">{{ $entry->posted_at->format('d M Y H:i') }}</span>
                    </div>
                    @endif
                </div>

                @if($entry->description)
                    <div class="mt-3">
                        <h6>Description:</h6>
                        <p class="text-muted">{{ $entry->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Journal Lines -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5>Journal Lines</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Account</th>
                                <th>Description</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entry->lines as $line)
                                <tr>
                                    <td>
                                        <strong>{{ $line->account->account_code }}</strong><br>
                                        <small>{{ $line->account->account_name }}</small>
                                    </td>
                                    <td>{{ $line->description ?? $entry->description }}</td>
                                    <td class="text-end">{{ $line->debit > 0 ? number_format($line->debit, 2) : '-' }}</td>
                                    <td class="text-end">{{ $line->credit > 0 ? number_format($line->credit, 2) : '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">Totals:</td>
                                <td class="text-end">{{ number_format($entry->total_debit, 2) }}</td>
                                <td class="text-end">{{ number_format($entry->total_credit, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function postEntry() {
    if(confirm('Are you sure you want to post this journal entry? This action cannot be undone.')) {
        document.getElementById('post-form').submit();
    }
}

function cancelEntry() {
    if(confirm('Are you sure you want to cancel this journal entry?')) {
        document.getElementById('cancel-form').submit();
    }
}
</script>
@endpush