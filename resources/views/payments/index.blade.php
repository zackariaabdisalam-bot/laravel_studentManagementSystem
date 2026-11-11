@extends('layouts.app')

@section('title', 'Payments')

@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Payments</h1>
                <p class="text-secondary mb-0">Record and review student payments.</p>
            </div>
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>Record Payment
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle me-2" aria-hidden="true"></i>{{ session('success') }}
                @if (str_contains(session('success'), 'STK Push'))
                    <div class="small mt-1">This payment will appear in the report after Safaricom confirms it. Refresh this page to see the updated status.</div>
                @endif
            </div>
        @endif

        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-3 p-md-4">
                <form method="GET" action="{{ route('payments.index') }}" class="row g-2 align-items-end mb-4">
                    <div class="col-12 col-md-5">
                        <label for="search" class="form-label">Search students</label>
                        <input
                            type="search"
                            name="search"
                            id="search"
                            value="{{ request('search') }}"
                            class="form-control"
                            placeholder="Name or student number"
                        >
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="method-filter" class="form-label">Method</label>
                        <select name="method" id="method-filter" class="form-select">
                            <option value="">All methods</option>
                            @foreach ($methods as $methodKey => $methodLabel)
                                <option value="{{ $methodKey }}" @selected(request('method') === $methodKey)>{{ $methodLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="status-filter" class="form-label">Status</label>
                        <select name="status" id="status-filter" class="form-select">
                            <option value="">All statuses</option>
                            <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">Filter</button>
                        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Reset</a>
                    </div>
                </form>
                @include('payments._table', ['tableTitle' => 'All Transactions'])
            </div>
        </section>

        @if ($mpesaRequests->isNotEmpty())
            <section class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body p-3 p-md-4">
                    <h2 class="h5 fw-bold mb-1">M-Pesa Requests</h2>
                    <p class="small text-secondary mb-3">Pending requests are not counted as paid until Safaricom confirms them.</p>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Student Fee</th>
                                    <th>Phone</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                    <th>Request</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($mpesaRequests as $request)
                                    <tr>
                                        <td>{{ trim(($request->fee?->student?->first_name ?? '').' '.($request->fee?->student?->last_name ?? '')) }} — {{ $request->fee?->title ?? 'Fee unavailable' }}</td>
                                        <td class="text-nowrap">{{ $request->phone }}</td>
                                        <td class="text-end text-nowrap">KSh {{ number_format((float) $request->amount, 2) }}</td>
                                        <td>
                                            <span class="badge rounded-pill {{ $request->status === 'pending' ? 'text-bg-warning' : 'text-bg-danger' }}">{{ ucfirst($request->status) }}</span>
                                        </td>
                                        <td class="small text-secondary">{{ $request->result_description ?: $request->account_reference }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <style>
        .report-table th,
        .report-table td {
            padding: 0.7rem;
            font-size: 0.875rem;
        }
    </style>
@endsection
