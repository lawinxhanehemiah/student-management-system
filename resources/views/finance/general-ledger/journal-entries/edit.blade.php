@extends('layouts.financecontroller')

@section('title', 'Edit Journal Entry')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Edit Journal Entry</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.general-ledger.journal-entries.index') }}">Journal Entries</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.general-ledger.journal-entries.show', $entry->id) }}">{{ $entry->journal_number }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.general-ledger.journal-entries.update', $entry->id) }}" method="POST" id="journalForm">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="form-section">
                        <h5 class="section-title">Journal Information</h5>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="journal_number" class="form-label">Journal Number</label>
                                <input type="text" class="form-control" id="journal_number" 
                                       value="{{ $entry->journal_number }}" readonly disabled>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="entry_date" class="form-label required">Entry Date</label>
                                <input type="date" class="form-control @error('entry_date') is-invalid @enderror" 
                                       id="entry_date" name="entry_date" value="{{ old('entry_date', $entry->entry_date->format('Y-m-d')) }}" required>
                                @error('entry_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="type" class="form-label required">Entry Type</label>
                                <select class="form-select @error('type') is-invalid @enderror" 
                                        id="type" name="type" required>
                                    <option value="manual" {{ old('type', $entry->type) == 'manual' ? 'selected' : '' }}>Manual</option>
                                    <option value="system" {{ old('type', $entry->type) == 'system' ? 'selected' : '' }}>System</option>
                                    <option value="recurring" {{ old('type', $entry->type) == 'recurring' ? 'selected' : '' }}>Recurring</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="description" class="form-label required">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" name="description" rows="2" required>{{ old('description', $entry->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Journal Lines -->
                    <div class="form-section mt-4">
                        <h5 class="section-title">Journal Lines</h5>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" id="journalLines">
                                <thead>
                                    <tr>
                                        <th>Account</th>
                                        <th>Description</th>
                                        <th class="text-end">Debit</th>
                                        <th class="text-end">Credit</th>
                                        <th width="50">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entry->lines as $index => $line)
                                        <tr>
                                            <td>
                                                <select name="lines[{{ $index }}][account_id]" class="form-select account-select" required>
                                                    <option value="">Select Account</option>
                                                    @foreach($accounts as $account)
                                                        <option value="{{ $account->id }}" 
                                                            data-type="{{ $account->account_type }}"
                                                            {{ old("lines.$index.account_id", $line->account_id) == $account->id ? 'selected' : '' }}>
                                                            {{ $account->account_code }} - {{ $account->account_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <input type="text" name="lines[{{ $index }}][description]" 
                                                       class="form-control" placeholder="Optional"
                                                       value="{{ old("lines.$index.description", $line->description) }}">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="lines[{{ $index }}][debit]" 
                                                       class="form-control text-end debit-input" 
                                                       value="{{ old("lines.$index.debit", $line->debit) }}" min="0">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" name="lines[{{ $index }}][credit]" 
                                                       class="form-control text-end credit-input" 
                                                       value="{{ old("lines.$index.credit", $line->credit) }}" min="0">
                                            </td>
                                            <td>
                                                @if($loop->index >= 2)
                                                    <button type="button" class="btn btn-sm btn-danger remove-line">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end">Totals:</th>
                                        <th class="text-end" id="totalDebit">{{ number_format($entry->total_debit, 2) }}</th>
                                        <th class="text-end" id="totalCredit">{{ number_format($entry->total_credit, 2) }}</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="2" class="text-end">Difference:</th>
                                        <th colspan="2" class="text-end" id="difference">0.00</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-secondary" id="addLine">
                                <i class="fas fa-plus me-2"></i>Add Line
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Info Card -->
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Journal Entry Rules
                                </h6>
                                <ul class="small text-muted">
                                    <li>Each journal must have at least 2 lines</li>
                                    <li>Total Debits must equal Total Credits</li>
                                    <li>Each line must have either Debit or Credit (not both)</li>
                                    <li>Lines cannot have both Debit and Credit zero</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Update Journal Entry
                </button>
                <a href="{{ route('finance.general-ledger.journal-entries.show', $entry->id) }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let lineIndex = {{ count($entry->lines) }};

$(document).ready(function() {
    $('#addLine').click(function() {
        addLine();
    });

    $(document).on('click', '.remove-line', function() {
        if($('#journalLines tbody tr').length > 2) {
            $(this).closest('tr').remove();
            calculateTotals();
        } else {
            alert('Journal must have at least 2 lines');
        }
    });

    $(document).on('input', '.debit-input, .credit-input', function() {
        const row = $(this).closest('tr');
        const debit = parseFloat(row.find('.debit-input').val()) || 0;
        const credit = parseFloat(row.find('.credit-input').val()) || 0;

        // Clear the other field if both have values
        if(debit > 0 && credit > 0) {
            if($(this).hasClass('debit-input')) {
                row.find('.credit-input').val(0);
            } else {
                row.find('.debit-input').val(0);
            }
        }

        calculateTotals();
    });

    calculateTotals();

    $('#journalForm').submit(function(e) {
        const totalDebit = parseFloat($('#totalDebit').text()) || 0;
        const totalCredit = parseFloat($('#totalCredit').text()) || 0;

        if(Math.abs(totalDebit - totalCredit) > 0.01) {
            e.preventDefault();
            alert('Journal entry is not balanced. Debits must equal credits.');
            return false;
        }

        return true;
    });
});

function addLine() {
    const newRow = `
        <tr>
            <td>
                <select name="lines[${lineIndex}][account_id]" class="form-select account-select" required>
                    <option value="">Select Account</option>
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}" data-type="{{ $account->account_type }}">
                            {{ $account->account_code }} - {{ $account->account_name }}
                        </option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="lines[${lineIndex}][description]" class="form-control" placeholder="Optional">
            </td>
            <td>
                <input type="number" step="0.01" name="lines[${lineIndex}][debit]" 
                       class="form-control text-end debit-input" value="0" min="0">
            </td>
            <td>
                <input type="number" step="0.01" name="lines[${lineIndex}][credit]" 
                       class="form-control text-end credit-input" value="0" min="0">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-line">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    `;
    
    $('#journalLines tbody').append(newRow);
    lineIndex++;
}

function calculateTotals() {
    let totalDebit = 0;
    let totalCredit = 0;

    $('#journalLines tbody tr').each(function() {
        totalDebit += parseFloat($(this).find('.debit-input').val()) || 0;
        totalCredit += parseFloat($(this).find('.credit-input').val()) || 0;
    });

    $('#totalDebit').text(totalDebit.toFixed(2));
    $('#totalCredit').text(totalCredit.toFixed(2));

    const difference = totalDebit - totalCredit;
    $('#difference').text(difference.toFixed(2));

    // Highlight difference if not zero
    if(Math.abs(difference) > 0.01) {
        $('#difference').addClass('text-danger fw-bold');
    } else {
        $('#difference').removeClass('text-danger fw-bold');
    }
}
</script>
@endpush