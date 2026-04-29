{{-- resources/views/superadmin/results/transcript.blade.php --}}

@extends('layouts.superadmin')

@section('title', 'Student Transcript')

@section('content')
<div class="container-fluid">
    <div class="transcript-wrapper">
        <div class="row">
            
            {{-- SIDEBAR - Year Selector --}}
            <div class="col-md-3 d-print-none">
                <div class="year-selector">
                    <div class="d-flex justify-content-between align-items-center py-3 border-bottom mb-2">
                        <span class="text-secondary fw-normal">Year of Study</span>
                        <i class="feather-chevron-down small text-muted"></i>
                    </div>
                    <div class="list-group list-group-flush border-0">
                        @foreach($results as $index => $year)
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 year-link {{ $loop->first ? 'active fw-bold text-primary' : 'text-primary' }}" 
                           data-year="{{ $index }}">
                            @php
                                $yearNum = $index + 1;
                                $suffix = 'th';
                                if($yearNum == 1) $suffix = 'st';
                                if($yearNum == 2) $suffix = 'nd';
                                if($yearNum == 3) $suffix = 'rd';
                            @endphp
                            {{ $yearNum }}<sup>{{ $suffix }}</sup> Year Results - {{ $year['year_name'] }}
                        </a>
                        @endforeach
                        <a href="#" class="list-group-item list-group-item-action border-0 px-0 py-2 text-dark view-all">
                            View All
                        </a>
                    </div>
                </div>
            </div>

            {{-- MAIN CONTENT --}}
            <div class="col-md-9 ps-3 border-start">
                
                {{-- Year Filter Select (Mobile) --}}
                <div class="d-print-none d-md-none mb-3">
                    <select id="yearSelect" class="form-select">
                        @foreach($results as $index => $year)
                            @php $yearNum = $index + 1; @endphp
                            <option value="{{ $index }}">{{ $yearNum }}{{ $yearNum == 1 ? 'st' : ($yearNum == 2 ? 'nd' : ($yearNum == 3 ? 'rd' : 'th')) }} Year - {{ $year['year_name'] }}</option>
                        @endforeach
                        <option value="all">View All</option>
                    </select>
                </div>

                {{-- Results Container --}}
                <div id="resultsContainer">
                    @foreach($results as $index => $year)
                    <div class="year-results" data-year="{{ $index }}" style="display: {{ $loop->first ? 'block' : 'none' }};">
                        
                        {{-- Header --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="results-title mb-0">Examination Results: {{ $year['year_name'] }}</h5>
                            <div class="d-print-none d-flex gap-2">
                                <button class="btn btn-link text-dark p-0" onclick="window.print()">
                                    <i class="feather-printer"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Semester 1 Table --}}
                        <div class="semester-block mb-4">
                            <h6 class="fw-bold mb-2 text-dark">Semester 1</h6>
                            <div class="table-responsive">
                                <table class="table transcript-table">
                                    <thead style="background-color: #f8f9fa; color: #000000; border-bottom: 2px solid #dee2e6;">
                                        <tr>
                                            <th style="width: 8%;">Code</th>
                                            <th style="width: 30%;">Module Name</th>
                                            <th style="width: 7%;">Type</th>
                                            <th style="width: 7%;">CW</th>
                                            <th style="width: 7%;">Exam</th>
                                            <th style="width: 7%;">SUP</th>
                                            <th style="width: 7%;">Total</th>
                                            <th style="width: 7%;">Credits</th>
                                            <th style="width: 7%;">Grade</th>
                                            <th style="width: 7%;">Points</th>
                                            <th style="width: 7%;">Remarks</th>
                                            <th style="width: 7%;">GPA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $sem1 = $year['semester_1'] ?? []; @endphp
                                        @forelse($sem1 as $module)
                                        @php
                                            // Points = Grade Point × Credits
                                            $points = ($module['grade_point'] ?? 0) * ($module['credits'] ?? 0);
                                        @endphp
                                        <tr>
                                            <td>{{ $module['module_code'] ?? '—' }}</td>
                                            <td class="module-name-cell">{{ $module['module_name'] ?? '' }}</td>
                                            <td>{{ $module['type'] ?? '' }}</td>
                                            <td>{{ number_format((float) ($module['cw_score'] ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($module['exam_score'] ?? 0), 1) }}</td>
                                            <td>{{ $module['sup_score'] ?? '' }}</td>
                                            <td><strong>{{ number_format((float) ($module['total_score'] ?? 0), 1) }}</strong></td>
                                            <td>{{ $module['credits'] ?? 0 }}</td>
                                            <td><strong style="color: #000000;">{{ $module['grade'] ?? '' }}</strong></td>
                                            <td>{{ number_format($points, 2) }}</td>
                                            <td class="fw-bold">{{ $module['remark'] ?? 'Pass' }}</td>
                                            <td class="text-center"></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted py-3">
                                                No results for Semester 1
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    @if(count($sem1) > 0)
                                    <tfoot style="background-color: #f8f9fa;">
                                        <tr class="fw-bold">
                                            <td colspan="6" class="text-end">Semester Total:</td>
                                            <td><strong></strong></td>
                                            <td><strong>{{ collect($sem1)->sum('credits') }}</strong></td>
                                            <td colspan="2"><strong>{{ number_format(collect($sem1)->sum(function($m) { return ($m['grade_point'] ?? 0) * ($m['credits'] ?? 0); }), 2) }}</strong></td>
                                            <td colspan="2" class="text-center"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="11" class="text-end">Semester GPA:</td>
                                            <td class="fw-bold">{{ number_format((float) ($year['gpa_semester_1'] ?? 0), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>

                        {{-- Semester 2 Table --}}
                        <div class="semester-block mb-4">
                            <h6 class="fw-bold mb-2 text-dark">Semester 2</h6>
                            <div class="table-responsive">
                                <table class="table transcript-table">
                                    <thead style="background-color: #f8f9fa; color: #000000; border-bottom: 2px solid #dee2e6;">
                                        <tr>
                                            <th style="width: 8%;">Code</th>
                                            <th style="width: 28%;">Module Name</th>
                                            <th style="width: 6%;">Type</th>
                                            <th style="width: 6%;">CW</th>
                                            <th style="width: 6%;">Exam</th>
                                            <th style="width: 6%;">SUP</th>
                                            <th style="width: 6%;">Total</th>
                                            <th style="width: 6%;">Credits</th>
                                            <th style="width: 6%;">Grade</th>
                                            <th style="width: 6%;">Points</th>
                                            <th style="width: 8%;">Remarks</th>
                                            <th style="width: 6%;">GPA</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $sem2 = $year['semester_2'] ?? []; @endphp
                                        @forelse($sem2 as $module)
                                        @php
                                            $points = ($module['grade_point'] ?? 0) * ($module['credits'] ?? 0);
                                        @endphp
                                        <tr>
                                            <td>{{ $module['module_code'] ?? '—' }}</td>
                                            <td class="module-name-cell">{{ $module['module_name'] ?? '—' }}</td>
                                            <td>{{ $module['type'] ?? '—' }}</td>
                                            <td>{{ number_format((float) ($module['cw_score'] ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($module['exam_score'] ?? 0), 1) }}</td>
                                            <td>{{ $module['sup_score'] ?? '—' }}</td>
                                            <td><strong>{{ number_format((float) ($module['total_score'] ?? 0), 1) }}</strong></td>
                                            <td>{{ $module['credits'] ?? 0 }}</td>
                                            <tr><strong style="color: #000000;">{{ $module['grade'] ?? '—' }}</strong></td>
                                            <td>{{ number_format($points, 2) }}</td>
                                            <td class="fw-bold">{{ $module['remark'] ?? 'Pass' }}</td>
                                            <td class="text-center">—</td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="12" class="text-center text-muted py-3">
                                                No results for Semester 2
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                    @if(count($sem2) > 0)
                                    <tfoot style="background-color: #f8f9fa;">
                                        <tr class="fw-bold">
                                            <td colspan="6" class="text-end">Semester Total:</td>
                                            <td><strong></strong></td>
                                            <td><strong>{{ collect($sem2)->sum('credits') }}</strong></td>
                                            <td colspan="2"><strong>{{ number_format(collect($sem2)->sum(function($m) { return ($m['grade_point'] ?? 0) * ($m['credits'] ?? 0); }), 2) }}</strong></td>
                                            <td colspan="2" class="text-center">—</td>
                                        </tr>
                                        <tr>
                                            <td colspan="11" class="text-end">Semester GPA:</td>
                                            <td class="fw-bold">{{ number_format((float) ($year['gpa_semester_2'] ?? 0), 2) }}</td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
                            </div>
                        </div>

                        
                    </div>
                    @endforeach

                    {{-- View All Results --}}
                    <div id="allResults" style="display: none;">
                        @foreach($results as $index => $year)
                        <div class="year-results-all mb-5">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="results-title mb-0">Examination Results: {{ $year['year_name'] }}</h5>
                            </div>
                            
                            {{-- Semester 1 --}}
                            <div class="semester-block mb-4">
                                <h6 class="fw-bold mb-2 text-dark">Semester 1</h6>
                                <div class="table-responsive">
                                    <table class="table transcript-table">
                                        <thead style="background-color: #f8f9fa; color: #000000;">
                                            <tr>
                                                <th>Code</th>
                                                <th style="width: 30%;">Module Name</th>
                                                <th>CW</th>
                                                <th>Exam</th>
                                                <th>SUP</th>
                                                <th>Total</th>
                                                <th>Credits</th>
                                                <th>Grade</th>
                                                <th>Points</th>
                                                <th>Remarks</th>
                                                <th>GPA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($year['semester_1'] ?? [] as $module)
                                            @php
                                                $points = ($module['grade_point'] ?? 0) * ($module['credits'] ?? 0);
                                            @endphp
                                            <tr>
                                                <td>{{ $module['module_code'] ?? '—' }}</td>
                                                <td class="module-name-cell">{{ $module['module_name'] ?? '' }}</td>
                                                <td>{{ number_format((float) ($module['cw_score'] ?? 0), 2) }}</td>
                                                <td>{{ number_format((float) ($module['exam_score'] ?? 0), 1) }}</td>
                                                <td>{{ $module['sup_score'] ?? '' }}</td>
                                                <td><strong>{{ number_format((float) ($module['total_score'] ?? 0), 1) }}</strong></td>
                                                <td>{{ $module['credits'] ?? 0 }}</td>
                                                <td><strong style="color: #000000;">{{ $module['grade'] ?? '' }}</strong></td>
                                                <td>{{ number_format($points, 2) }}</td>
                                                <td class="fw-bold">{{ $module['remark'] ?? 'Pass' }}</td>
                                                <td class="text-center"></td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">No results</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Semester 2 --}}
                            <div class="semester-block mb-4">
                                <h6 class="fw-bold mb-2 text-dark">Semester 2</h6>
                                <div class="table-responsive">
                                    <table class="table transcript-table">
                                        <thead style="background-color: #f8f9fa; color: #000000;">
                                            <tr>
                                                <th>Code</th>
                                                <th style="width: 30%;">Module Name</th>
                                                <th>CW</th>
                                                <th>Exam</th>
                                                <th>SUP</th>
                                                <th>Total</th>
                                                <th>Credits</th>
                                                <th>Grade</th>
                                                <th>Points</th>
                                                <th>Remarks</th>
                                                <th>GPA</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($year['semester_2'] ?? [] as $module)
                                            @php
                                                $points = ($module['grade_point'] ?? 0) * ($module['credits'] ?? 0);
                                            @endphp
                                            <tr>
                                                <td>{{ $module['module_code'] ?? '—' }}</td>
                                                <td class="module-name-cell">{{ $module['module_name'] ?? '—' }}</td>
                                                <td>{{ number_format((float) ($module['cw_score'] ?? 0), 2) }}</td>
                                                <td>{{ number_format((float) ($module['exam_score'] ?? 0), 1) }}</td>
                                                <td>{{ $module['sup_score'] ?? '—' }}</td>
                                                <td><strong>{{ number_format((float) ($module['total_score'] ?? 0), 1) }}</strong></td>
                                                <td>{{ $module['credits'] ?? 0 }}</td>
                                                <td><strong style="color: #000000;">{{ $module['grade'] ?? '—' }}</strong></td>
                                                <td>{{ number_format($points, 2) }}</td>
                                                <td class="fw-bold">{{ $module['remark'] ?? 'Pass' }}</td>
                                                <td class="text-center">—</td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="11" class="text-center text-muted">No results</td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach

                        {{-- CGPA Summary --}}
                        <div class="row mt-4">
                            <div class="col-md-6 offset-md-6">
                                <div class="summary-box">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Credits (All Years):</span>
                                        <strong>{{ $total_credits ?? 0 }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span>Total Points (All Years):</span>
                                        <strong>{{ number_format((float) ($total_points ?? 0), 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1 pt-1 border-top">
                                        <span>CGPA:</span>
                                        <strong class="text-primary">{{ number_format((float) ($cgpa ?? 0), 2) }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span>Classification:</span>
                                        <strong>{{ $classification ?? 'N/A' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .transcript-wrapper {
        background-color: #ffffff;
        font-family: 'Inter', 'Segoe UI', sans-serif;
        min-height: 100vh;
        padding: 20px;
    }
    .results-title {
        color: #555;
        font-weight: 400;
        font-size: 1rem;
    }
    .transcript-table {
        border-collapse: collapse !important;
        width: 100%;
        margin-bottom: 1rem;
        border: 1px solid #dee2e6;
        font-size: 12px;
    }
    .transcript-table thead th {
        border-bottom: 2px solid #dee2e6 !important;
        font-weight: 600;
        color: #000000;
        font-size: 12px;
        padding: 6px 4px;
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
    }
    .transcript-table tbody td {
        border-bottom: 1px solid #f0f0f0;
        font-size: 12px;
        color: #333;
        padding: 6px 4px;
        vertical-align: top;
        border: 1px solid #dee2e6;
    }
    .transcript-table tfoot td {
        background-color: #f8f9fa;
        padding: 6px 4px;
        border: 1px solid #dee2e6;
        font-size: 12px;
    }
    .module-name-cell {
        word-wrap: break-word !important;
        white-space: normal !important;
        word-break: break-word !important;
        max-width: 200px;
        min-width: 120px;
    }
    .list-group-item {
        background: transparent;
        font-size: 13px;
        cursor: pointer;
    }
    .list-group-item.active {
        background: transparent;
        color: #0d6efd !important;
        font-weight: bold;
    }
    .year-link:hover {
        background: transparent;
        color: #0d6efd !important;
    }
    .summary-box {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 10px 12px;
        border: 1px solid #e9ecef;
        font-size: 12px;
    }
    .badge {
        font-size: 11px;
        padding: 3px 8px;
    }
    @media print {
        .d-print-none { display: none !important; }
        .col-md-9 { width: 100% !important; border: none !important; }
        .transcript-wrapper { padding: 0; }
        .summary-box { background: none; border: 1px solid #ddd; }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const yearLinks = document.querySelectorAll('.year-link');
    const viewAllLink = document.querySelector('.view-all');
    const yearResults = document.querySelectorAll('.year-results');
    const allResultsDiv = document.getElementById('allResults');
    const yearSelect = document.getElementById('yearSelect');

    function showYear(index) {
        yearResults.forEach(el => el.style.display = 'none');
        if (allResultsDiv) allResultsDiv.style.display = 'none';
        if (yearResults[index]) yearResults[index].style.display = 'block';
        
        yearLinks.forEach((link, i) => {
            if (i == index) {
                link.classList.add('active', 'fw-bold', 'text-primary');
                link.classList.remove('text-primary');
            } else {
                link.classList.remove('active', 'fw-bold');
                link.classList.add('text-primary');
            }
        });
        if (viewAllLink) viewAllLink.classList.remove('active', 'fw-bold');
    }

    function showAll() {
        yearResults.forEach(el => el.style.display = 'none');
        if (allResultsDiv) allResultsDiv.style.display = 'block';
        yearLinks.forEach(link => {
            link.classList.remove('active', 'fw-bold');
            link.classList.add('text-primary');
        });
        if (viewAllLink) viewAllLink.classList.add('active', 'fw-bold');
    }

    yearLinks.forEach((link, index) => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            showYear(index);
            if (yearSelect) yearSelect.value = index;
        });
    });

    if (viewAllLink) {
        viewAllLink.addEventListener('click', (e) => {
            e.preventDefault();
            showAll();
            if (yearSelect) yearSelect.value = 'all';
        });
    }

    if (yearSelect) {
        yearSelect.addEventListener('change', function() {
            if (this.value === 'all') showAll();
            else showYear(parseInt(this.value));
        });
    }

    if (yearResults.length > 0) showYear(0);
});
</script>
@endsection