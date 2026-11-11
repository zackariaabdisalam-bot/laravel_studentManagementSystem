@extends('layouts.app')

@section('title', 'Payment Report')

@section('content')
    <div class="container-fluid payment-report-page">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 no-print">
            <div>
                <h1 class="h3 fw-bold mb-1">Payment Report</h1>
                <p class="text-secondary mb-0">View and analyze completed student payments</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer me-2" aria-hidden="true"></i>Print Report
                </button>
                <a class="btn btn-success"
                   href="{{ route('reports.index', ['from' => $filters['from'], 'to' => $filters['to'], 'method' => $filters['method'], 'export' => 'csv']) }}">
                    <i class="bi bi-download me-2" aria-hidden="true"></i>Export CSV
                </a>
            </div>
        </div>

        <article id="payment-report-document" class="payment-report-document bg-white rounded-4 shadow-sm p-3 p-md-4">
            <header class="text-center border-bottom pb-3 mb-4">
                <div class="fw-bold text-uppercase report-brand">Student Management System</div>
                <h2 class="h4 text-uppercase fw-bold mt-2 mb-1">Payment Report</h2>
                <p class="text-secondary small mb-0">Generated on {{ $generatedAt }}</p>
            </header>

            <section class="mb-4" aria-labelledby="report-filters-title">
                <h3 id="report-filters-title" class="h6 fw-bold mb-3">Report Filters</h3>
                <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end report-filter-form">
                    <div class="col-12 col-md-4">
                        <label for="report_from" class="form-label fw-semibold">From Date</label>
                        <input id="report_from" type="date" name="from" value="{{ $filters['from'] }}" class="form-control @error('from') is-invalid @enderror">
                        @error('from')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="report_to" class="form-label fw-semibold">To Date</label>
                        <input id="report_to" type="date" name="to" value="{{ $filters['to'] }}" class="form-control @error('to') is-invalid @enderror">
                        @error('to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12 col-md-4">
                        <label for="report_method" class="form-label fw-semibold">Payment Method</label>
                        <select id="report_method" name="method" class="form-select">
                            <option value="all" @selected($filters['method'] === 'all')>All Methods</option>
                            @foreach ($methods as $value => $label)
                                <option value="{{ $value }}" @selected($filters['method'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-filter me-1" aria-hidden="true"></i>Generate Report
                        </button>
                        <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary">Clear Filters</a>
                    </div>
                </form>
            </section>

            <section class="mb-4" aria-label="Payment summary">
                <div class="row g-3 report-summary">
                    <div class="col-12 col-sm-6 col-xl-4">
                        <div class="summary-card summary-total h-100 rounded-3 p-3">
                            <div class="small text-secondary">Total Collected</div>
                            <div class="h4 fw-bold text-success mb-0">KSh {{ number_format($totalCollected, 2) }}</div>
                        </div>
                    </div>
                    @foreach ($methods as $method => $label)
                        <div class="col-6 col-sm-3 col-xl-2">
                            <div class="summary-card h-100 rounded-3 p-3">
                                <div class="small text-secondary">{{ $label }}</div>
                                <div class="fw-bold">KSh {{ number_format($methodTotals[$method], 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                    <div class="col-6 col-sm-3 col-xl-2">
                        <div class="summary-card h-100 rounded-3 p-3">
                            <div class="small text-secondary">Total Payments</div>
                            <div class="fw-bold">{{ $payments->count() }}</div>
                        </div>
                    </div>
                </div>
            </section>

            @include('payments._table', ['tableTitle' => 'Detailed Transactions'])
        </article>
    </div>

    <style>
        .payment-report-document {
            color: #1f2937;
        }

        .report-brand {
            letter-spacing: 0.08em;
        }

        .summary-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }

        .summary-total {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .report-table th,
        .report-table td {
            padding: 0.7rem;
            font-size: 0.875rem;
        }

        @media print {
            @page {
                size: landscape;
                margin: 12mm;
            }

            body * {
                visibility: hidden !important;
            }

            #payment-report-document,
            #payment-report-document * {
                visibility: visible !important;
            }

            #payment-report-document {
                position: absolute;
                inset: 0 auto auto 0;
                width: 100%;
                padding: 0 !important;
                border: 0 !important;
                box-shadow: none !important;
            }

            .report-filter-form .btn,
            .report-table .badge {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .report-table {
                width: 100%;
                font-size: 9pt;
            }

            .report-table th,
            .report-table td {
                padding: 0.35rem;
                font-size: 8pt;
            }
        }
    </style>
@endsection
