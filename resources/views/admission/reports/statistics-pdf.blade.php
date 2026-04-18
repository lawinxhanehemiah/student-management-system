<!DOCTYPE html>
<html>
<head>
    <title>Admission Statistics {{ $year }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f5f5f5; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Admission Statistics Report</h1>
        <p>Year: {{ $year }}</p>
    </div>

    <h3>Applications by Status</h3>
    <table class="table">
        <thead>
            <tr><th>Status</th><th>Count</th><th>Percentage</th></tr>
        </thead>
        <tbody>
            @php $total = $applicationsByStatus->sum('total'); @endphp
            @foreach($applicationsByStatus as $status)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $status->status)) }}</td>
                    <td>{{ $status->total }}</td>
                    <td>{{ $total > 0 ? round(($status->total / $total) * 100, 1) : 0 }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Top Programmes</h3>
    <table class="table">
        <thead><tr><th>Programme</th><th>Applications</th></tr></thead>
        <tbody>
            @foreach($applicationsByProgramme as $prog)
                <tr><td>{{ $prog->programme }}</td><td>{{ $prog->total }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h3>Gender Distribution</h3>
    <table class="table">
        <thead><tr><th>Gender</th><th>Count</th></tr></thead>
        <tbody>
            @foreach($genderDistribution as $gender)
                <tr><td>{{ $gender->gender }}</td><td>{{ $gender->total }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>