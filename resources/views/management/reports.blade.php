@extends('layouts.app')

@section('title', 'Reports')

@section('content')
    <div class="container-fluid">
        <div class="mb-4">
            <h1 class="h3 fw-bold mb-1">Reports</h1>
            <p class="text-secondary mb-0">Review enrollment, attendance, and payments for a selected date range.</p>
        </div>

        <section class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end">
                    <div class="col-12 col-sm-5">
                        <label for="report_from" class="form-label fw-semibold">From date</label>
                        <input id="report_from" type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-12 col-sm-5">
                        <label for="report_to" class="form-label fw-semibold">To date</label>
                        <input id="report_to" type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="form-control">
                    </div>
                    <div class="col-12 col-sm-2 d-flex gap-2">
                        <button class="btn btn-primary flex-grow-1" type="submit">Filter</button>
                        <a class="btn btn-outline-secondary" href="{{ route('reports.index') }}">Clear</a>
                    </div>
                </form>
            </div>
        </section>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4">
                    <p class="text-secondary mb-1">Total students</p>
                    <div class="h3 fw-bold mb-0">{{ $totalStudents }}</div>
                </div></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4">
                    <p class="text-secondary mb-1">Total courses</p>
                    <div class="h3 fw-bold mb-0">{{ $totalCourses }}</div>
                </div></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4">
                    <p class="text-secondary mb-1">Payments in selected period</p>
                    <div class="h3 fw-bold mb-0">KSh {{ number_format((float) $feesCollected, 2) }}</div>
                </div></div>
            </div>
        </div>

        <section class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-0 p-4"><h2 class="h5 fw-bold mb-0">Attendance by status</h2></div>
            <div class="card-body pt-0">
                <div class="row g-3">
                    @foreach (['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'] as $status => $label)
                        <div class="col-6 col-lg-3">
                            <div class="rounded-3 bg-light p-3">
                                <div class="text-secondary small">{{ $label }}</div>
                                <div class="h4 fw-bold mb-0">{{ $attendanceCounts[$status] ?? 0 }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-0 p-4"><h2 class="h5 fw-bold mb-0">Payments in selected period</h2></div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr>
                        <th class="px-3 py-3">Date</th><th class="px-3 py-3">Student</th><th class="px-3 py-3">Fee</th><th class="px-3 py-3">Method</th><th class="text-end px-3 py-3">Amount (KSh)</th>
                    </tr></thead>
                    <tbody>
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="px-3">{{ $payment->paid_at->format('M j, Y') }}</td>
                                <td class="px-3">{{ $payment->student->name }}</td>
                                <td class="px-3">{{ $payment->fee->title }}</td>
                                <td class="px-3">{{ ucfirst(str_replace('_', ' ', $payment->method)) }}</td>
                                <td class="text-end px-3">{{ number_format((float) $payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-5">No payments found in this date range.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($payments->hasPages())
                <div class="card-footer bg-white border-0 px-4">{{ $payments->links() }}</div>
            @endif
        </section>
    </div>
@endsection
