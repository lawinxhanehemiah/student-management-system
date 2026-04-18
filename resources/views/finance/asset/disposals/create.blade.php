@extends('layouts.financecontroller')

@section('title', 'Dispose Asset')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Dispose Asset</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('finance.asset.disposals.index') }}">Disposals</a></li>
                <li class="breadcrumb-item active">New Disposal</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('finance.asset.disposals.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-lg-8">
                    <!-- Disposal Information -->
                    <div class="form-section">
                        <h5 class="section-title">Disposal Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="asset_id" class="form-label required">Asset</label>
                                <select class="form-select @error('asset_id') is-invalid @enderror" 
                                        id="asset_id" name="asset_id" required>
                                    <option value="">Select Asset...</option>
                                    @foreach($assets as $ast)
                                        <option value="{{ $ast->id }}" 
                                            {{ (old('asset_id', $asset->id ?? '') == $ast->id) ? 'selected' : '' }}
                                            data-current-value="{{ $ast->current_value }}">
                                            {{ $ast->asset_tag }} - {{ $ast->name }} 
                                            (Current: {{ number_format($ast->current_value, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('asset_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="disposal_date" class="form-label required">Disposal Date</label>
                                <input type="date" class="form-control @error('disposal_date') is-invalid @enderror" 
                                       id="disposal_date" name="disposal_date" 
                                       value="{{ old('disposal_date', date('Y-m-d')) }}" required>
                                @error('disposal_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="disposal_method" class="form-label required">Disposal Method</label>
                                <select class="form-select @error('disposal_method') is-invalid @enderror" 
                                        id="disposal_method" name="disposal_method" required>
                                    <option value="">Select Method</option>
                                    <option value="sold" {{ old('disposal_method') == 'sold' ? 'selected' : '' }}>Sold</option>
                                    <option value="scrapped" {{ old('disposal_method') == 'scrapped' ? 'selected' : '' }}>Scrapped</option>
                                    <option value="donated" {{ old('disposal_method') == 'donated' ? 'selected' : '' }}>Donated</option>
                                    <option value="lost" {{ old('disposal_method') == 'lost' ? 'selected' : '' }}>Lost</option>
                                    <option value="stolen" {{ old('disposal_method') == 'stolen' ? 'selected' : '' }}>Stolen</option>
                                    <option value="other" {{ old('disposal_method') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('disposal_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="disposal_amount" class="form-label">Disposal Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">TZS</span>
                                    <input type="number" step="0.01" class="form-control @error('disposal_amount') is-invalid @enderror" 
                                           id="disposal_amount" name="disposal_amount" value="{{ old('disposal_amount') }}">
                                </div>
                                <small class="text-muted">Leave blank if no proceeds</small>
                                @error('disposal_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="authorized_by" class="form-label">Authorized By</label>
                                <input type="text" class="form-control @error('authorized_by') is-invalid @enderror" 
                                       id="authorized_by" name="authorized_by" value="{{ old('authorized_by') }}"
                                       placeholder="Name of authorizing person">
                                @error('authorized_by')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 mb-3">
                                <label for="reason" class="form-label">Reason for Disposal</label>
                                <textarea class="form-control @error('reason') is-invalid @enderror" 
                                          id="reason" name="reason" rows="3">{{ old('reason') }}</textarea>
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="info-card">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Disposal Impact
                                </h6>
                                <div id="assetInfo" style="display: none;">
                                    <p><strong>Current Value:</strong> <span id="currentValue">0.00</span></p>
                                    <p><strong>Purchase Cost:</strong> <span id="purchaseCost">0.00</span></p>
                                    <p><strong>Expected Gain/Loss:</strong> <span id="expectedGainLoss">0.00</span></p>
                                </div>
                                <hr>
                                <ul class="small text-muted">
                                    <li>Disposed assets cannot be edited</li>
                                    <li>Gain/Loss = Disposal Amount - Book Value</li>
                                    <li>Approval may be required for disposals</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions mt-4">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash-alt me-2"></i>Record Disposal
                </button>
                <a href="{{ route('finance.asset.disposals.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#asset_id').change(function() {
        const selected = $(this).find('option:selected');
        const currentValue = parseFloat(selected.data('current-value') || 0);
        
        if (currentValue > 0) {
            $('#currentValue').text(currentValue.toFixed(2));
            $('#assetInfo').show();
            calculateGainLoss();
        } else {
            $('#assetInfo').hide();
        }
    });

    $('#disposal_amount').on('input', function() {
        calculateGainLoss();
    });

    function calculateGainLoss() {
        const currentValue = parseFloat($('#currentValue').text()) || 0;
        const disposalAmount = parseFloat($('#disposal_amount').val()) || 0;
        const gainLoss = disposalAmount - currentValue;
        
        $('#expectedGainLoss').text(gainLoss.toFixed(2));
        $('#expectedGainLoss').removeClass('text-success text-danger');
        
        if (gainLoss > 0) {
            $('#expectedGainLoss').addClass('text-success');
        } else if (gainLoss < 0) {
            $('#expectedGainLoss').addClass('text-danger');
        }
    }

    // Trigger on page load if asset is selected
    if ($('#asset_id').val()) {
        $('#asset_id').trigger('change');
    }
});
</script>
@endpush