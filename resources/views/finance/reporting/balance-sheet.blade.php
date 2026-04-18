@extends('layouts.financecontroller')

@section('title', 'Balance Sheet')

@section('content')
<div class="page-header">
    <div class="page-title">
        <h4>Balance Sheet</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('finance.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Financial Reporting</a></li>
                <li class="breadcrumb-item active">Balance Sheet</li>
            </ol>
        </nav>
    </div>
    <div class="page-btn">
        <a href="#" onclick="window.print()" class="btn btn-info">
            <i class="fas fa-print me-2"></i>Print
        </a>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('finance.reporting.balance-sheet') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="fiscal_year_id" class="form-label">Fiscal Year</label>
                <select name="fiscal_year_id" id="fiscal_year_id" class="form-select" onchange="this.form.submit()">
                    <option value="">As of Date</option>
                    @foreach($fiscalYears as $year)
                        <option value="{{ $year->id }}" {{ request('fiscal_year_id') == $year->id ? 'selected' : '' }}>
                            {{ $year->name }} (End: {{ $year->end_date->format('d M Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="as_of_date" class="form-label">As of Date</label>
                <input type="date" class="form-control" name="as_of_date" id="as_of_date" value="{{ $asOfDate }}">
            </div>
            <div class="col-md-4">
                <label for="show_comparison" class="form-label">&nbsp;</label>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="show_comparison" id="show_comparison" value="1" {{ $showComparison ? 'checked' : '' }} onchange="this.form.submit()">
                    <label class="form-check-label" for="show_comparison">Show Previous Year Comparison</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sync-alt me-2"></i>Generate Report
                </button>
                <a href="{{ route('finance.reporting.balance-sheet') }}" class="btn btn-secondary">
                    <i class="fas fa-undo me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Report Header -->
<div class="text-center mb-4">
    <h3>BALANCE SHEET</h3>
    <p class="text-muted">
        As of <strong>{{ \Carbon\Carbon::parse($asOfDate)->format('F d, Y') }}</strong>
    </p>
</div>

<!-- Balance Check Alert -->
@if(abs($totalAssets - $totalLiabilitiesEquity) > 0.01)
    <div class="alert alert-danger mb-4">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Balance Sheet is NOT Balanced!</strong> 
        Assets ({{ number_format($totalAssets, 2) }}) ≠ Liabilities + Equity ({{ number_format($totalLiabilitiesEquity, 2) }})
        Difference: {{ number_format($totalAssets - $totalLiabilitiesEquity, 2) }}
    </div>
@else
    <div class="alert alert-success mb-4">
        <i class="fas fa-check-circle me-2"></i>
        <strong>Balance Sheet is Balanced ✓</strong>
        Assets = Liabilities + Equity = {{ number_format($totalAssets, 2) }}
    </div>
@endif

<!-- Balance Sheet Table -->
<div class="row">
    <!-- ASSETS -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">ASSETS</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Account</th>
                            <th class="text-end">Current</th>
                            @if($showComparison)
                                <th class="text-end">Previous Year</th>
                                <th class="text-end">Change</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentAssetTotal = 0; @endphp
                        @foreach($assets as $asset)
                            @php $currentAssetTotal += $asset->balance; @endphp
                            <tr>
                                <td>
                                    <strong>{{ $asset->account_code }}</strong><br>
                                    <small>{{ $asset->account_name }}</small>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($asset->balance, 2) }}</td>
                                @if($showComparison)
                                    @php
                                        $prevAsset = $previousAssets?->firstWhere('id', $asset->id);
                                        $prevBalance = $prevAsset ? $prevAsset->balance : 0;
                                        $change = $asset->balance - $prevBalance;
                                        $changePercent = $prevBalance != 0 ? round(($change / $prevBalance) * 100, 2) : 0;
                                    @endphp
                                    <td class="text-end">{{ number_format($prevBalance, 2) }}</td>
                                    <td class="text-end {{ $change >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2) }}
                                        <br><small>({{ $changePercent }}%)</small>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        <tr class="table-primary fw-bold">
                            <td>TOTAL ASSETS</td>
                            <td class="text-end">{{ number_format($totalAssets, 2) }}</td>
                            @if($showComparison)
                                <td class="text-end">{{ number_format($previousTotalAssets, 2) }}</td>
                                <td class="text-end">
                                    {{ number_format($totalAssets - $previousTotalAssets, 2) }}
                                    <br><small>({{ $previousTotalAssets != 0 ? round(($totalAssets - $previousTotalAssets) / $previousTotalAssets * 100, 2) : 0 }}%)</small>
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- LIABILITIES & EQUITY -->
    <div class="col-md-6">
        <!-- LIABILITIES -->
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">LIABILITIES</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Account</th>
                            <th class="text-end">Current</th>
                            @if($showComparison)
                                <th class="text-end">Previous Year</th>
                                <th class="text-end">Change</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentLiabilityTotal = 0; @endphp
                        @foreach($liabilities as $liability)
                            @php $currentLiabilityTotal += $liability->balance; @endphp
                            <tr>
                                <td>
                                    <strong>{{ $liability->account_code }}</strong><br>
                                    <small>{{ $liability->account_name }}</small>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($liability->balance, 2) }}</td>
                                @if($showComparison)
                                    @php
                                        $prevLiability = $previousLiabilities?->firstWhere('id', $liability->id);
                                        $prevBalance = $prevLiability ? $prevLiability->balance : 0;
                                        $change = $liability->balance - $prevBalance;
                                        $changePercent = $prevBalance != 0 ? round(($change / $prevBalance) * 100, 2) : 0;
                                    @endphp
                                    <td class="text-end">{{ number_format($prevBalance, 2) }}</td>
                                    <td class="text-end {{ $change >= 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2) }}
                                        <br><small>({{ $changePercent }}%)</small>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        <tr class="table-warning fw-bold">
                            <td>TOTAL LIABILITIES</td>
                            <td class="text-end">{{ number_format($totalLiabilities, 2) }}</td>
                            @if($showComparison)
                                <td class="text-end">{{ number_format($previousTotalLiabilities, 2) }}</td>
                                <td class="text-end">
                                    {{ number_format($totalLiabilities - $previousTotalLiabilities, 2) }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- EQUITY -->
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">EQUITY</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Account</th>
                            <th class="text-end">Current</th>
                            @if($showComparison)
                                <th class="text-end">Previous Year</th>
                                <th class="text-end">Change</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentEquityTotal = 0; @endphp
                        @foreach($equity as $eq)
                            @php $currentEquityTotal += $eq->balance; @endphp
                            <tr>
                                <td>
                                    <strong>{{ $eq->account_code }}</strong><br>
                                    <small>{{ $eq->account_name }}</small>
                                </td>
                                <td class="text-end fw-bold">{{ number_format($eq->balance, 2) }}</td>
                                @if($showComparison)
                                    @php
                                        $prevEquity = $previousEquity?->firstWhere('id', $eq->id);
                                        $prevBalance = $prevEquity ? $prevEquity->balance : 0;
                                        $change = $eq->balance - $prevBalance;
                                        $changePercent = $prevBalance != 0 ? round(($change / $prevBalance) * 100, 2) : 0;
                                    @endphp
                                    <td class="text-end">{{ number_format($prevBalance, 2) }}</td>
                                    <td class="text-end {{ $change >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $change >= 0 ? '+' : '' }}{{ number_format($change, 2) }}
                                        <br><small>({{ $changePercent }}%)</small>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                        <tr class="table-success fw-bold">
                            <td>TOTAL EQUITY</td>
                            <td class="text-end">{{ number_format($totalEquity, 2) }}</td>
                            @if($showComparison)
                                <td class="text-end">{{ number_format($previousTotalEquity, 2) }}</td>
                                <td class="text-end">
                                    {{ number_format($totalEquity - $previousTotalEquity, 2) }}
                                </td>
                            @endif
                        </tr>
                        
                        <!-- TOTAL LIABILITIES & EQUITY -->
                        <tr class="table-info fw-bold" style="border-top: 2px solid #000;">
                            <td>TOTAL LIABILITIES & EQUITY</td>
                            <td class="text-end">{{ number_format($totalLiabilitiesEquity, 2) }}</td>
                            @if($showComparison)
                                <td class="text-end">{{ number_format($previousTotalLiabilities + $previousTotalEquity, 2) }}</td>
                                <td class="text-end">
                                    {{ number_format($totalLiabilitiesEquity - ($previousTotalLiabilities + $previousTotalEquity), 2) }}
                                </td>
                            @endif
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart Section -->
<div class="card mt-4">
    <div class="card-header">
        <h5>Balance Sheet Composition</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <canvas id="assetsChart" height="200"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="liabilitiesEquityChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Assets Chart
    const assetsCtx = document.getElementById('assetsChart').getContext('2d');
    const assetLabels = {!! json_encode($assets->pluck('account_name')->values()) !!};
    const assetData = {!! json_encode($assets->pluck('balance')->values()) !!};
    
    new Chart(assetsCtx, {
        type: 'pie',
        data: {
            labels: assetLabels,
            datasets: [{
                data: assetData,
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(201, 203, 207, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Assets Composition'
                }
            }
        }
    });
    
    // Liabilities & Equity Chart
    const liabCtx = document.getElementById('liabilitiesEquityChart').getContext('2d');
    const liabLabels = [];
    const liabData = [];
    
    @foreach($liabilities as $liability)
        liabLabels.push('{{ $liability->account_name }}');
        liabData.push({{ $liability->balance }});
    @endforeach
    
    @foreach($equity as $eq)
        liabLabels.push('{{ $eq->account_name }}');
        liabData.push({{ $eq->balance }});
    @endforeach
    
    new Chart(liabCtx, {
        type: 'pie',
        data: {
            labels: liabLabels,
            datasets: [{
                data: liabData,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(255, 205, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                title: {
                    display: true,
                    text: 'Liabilities & Equity Composition'
                }
            }
        }
    });
});
</script>
@endpush