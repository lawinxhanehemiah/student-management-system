@extends('layouts.superadmin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="feather-credit-card text-primary"></i> 
            Fee Transactions
        </h1>
        <div>
            <button class="btn btn-success">
                <i class="feather-download"></i> Export
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Transaction Filters</h6>
        </div>
        <div class="card-body">
            <form class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="text" class="form-control daterange">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Programme</label>
                    <select class="form-control">
                        <option value="">All Programmes</option>
                        @foreach(\App\Models\Programme::active()->get() as $programme)
                            <option value="{{ $programme->id }}">{{ $programme->code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment Status</label>
                    <select class="form-control">
                        <option value="">All Status</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="overdue">Overdue</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Payment Method</label>
                    <select class="form-control">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="mobile">Mobile</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="feather-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                Transaction History
            </h6>
            <span class="text-success">
                Total: <strong>5,450,000/=</strong>
            </span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Programme</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>TX-001</td>
                            <td>15 Jan 2024</td>
                            <td>John Doe</td>
                            <td>BIT</td>
                            <td>Tuition Fee - Sem 1</td>
                            <td class="text-end">1,500,000/=</td>
                            <td><span class="badge bg-info">Bank</span></td>
                            <td><span class="badge bg-success">Paid</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="feather-file-text"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>TX-002</td>
                            <td>14 Jan 2024</td>
                            <td>Jane Smith</td>
                            <td>BBA</td>
                            <td>Registration Fee</td>
                            <td class="text-end">250,000/=</td>
                            <td><span class="badge bg-warning">Cash</span></td>
                            <td><span class="badge bg-success">Paid</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="feather-file-text"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>TX-003</td>
                            <td>13 Jan 2024</td>
                            <td>Mike Johnson</td>
                            <td>BIT</td>
                            <td>Tuition Fee - Sem 2</td>
                            <td class="text-end">1,500,000/=</td>
                            <td><span class="badge bg-success">Mobile</span></td>
                            <td><span class="badge bg-success">Paid</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="feather-file-text"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>TX-004</td>
                            <td>12 Jan 2024</td>
                            <td>Sarah Williams</td>
                            <td>BCOM</td>
                            <td>Examination Fee</td>
                            <td class="text-end">100,000/=</td>
                            <td><span class="badge bg-info">Bank</span></td>
                            <td><span class="badge bg-warning">Pending</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                    <i class="feather-file-text"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>TX-005</td>
                            <td>11 Jan 2024</td>
                            <td>David Brown</td>
                            <td>BIT</td>
                            <td>Late Payment Penalty</td>
                            <td class="text-end">75,000/=</td>
                            <td><span class="badge bg-success">Mobile</span></td>
                            <td><span class="badge bg-danger">Overdue</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary">
                                    <i class="feather-file-text"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                <nav>
                    <ul class="pagination">
                        <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                        <li class="page-item active"><a class="page-link" href="#">1</a></li>
                        <li class="page-item"><a class="page-link" href="#">2</a></li>
                        <li class="page-item"><a class="page-link" href="#">3</a></li>
                        <li class="page-item"><a class="page-link" href="#">Next</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Paid</h6>
                    <h3 class="card-text">4,200,000/=</h3>
                    <small>This month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Completed</h6>
                    <h3 class="card-text">38</h3>
                    <small>Transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6 class="card-title">Pending</h6>
                    <h3 class="card-text">12</h3>
                    <small>Transactions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h6 class="card-title">Overdue</h6>
                    <h3 class="card-text">5</h3>
                    <small>Transactions</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize date range picker
        $('.daterange').daterangepicker({
            opens: 'left',
            locale: {
                format: 'YYYY-MM-DD'
            }
        });
    });
</script>
@endsection