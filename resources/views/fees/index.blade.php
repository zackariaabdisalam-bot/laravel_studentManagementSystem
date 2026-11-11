@extends('layouts.app')

@section('title', 'Fees')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Fees</h2>
            <p class="text-muted mb-0">Create and manage student fee charges.</p>
        </div>
        <a href="{{ route('fees.create') }}" class="btn school-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Add Fee
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="py-3">#</th>
                            <th class="py-3">Student</th>
                            <th class="py-3">Fee</th>
                            <th class="py-3">Amount</th>
                            <th class="py-3">Paid</th>
                            <th class="py-3">Balance</th>
                            <th class="py-3">Due Date</th>
                            <th class="py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($fees as $fee)
                            @php($paidAmount = (float) ($fee->payments_sum_amount ?? 0))
                            <tr>
                                <td class="py-3">{{ $fees->firstItem() + $loop->index }}</td>
                                <td class="py-3">
                                    <strong>{{ $fee->student?->first_name }} {{ $fee->student?->last_name }}</strong>
                                    <div class="small text-muted">{{ $fee->student?->student_number ?? 'Student unavailable' }}</div>
                                </td>
                                <td class="py-3">
                                    <strong>{{ $fee->title }}</strong>
                                    @if ($fee->notes)
                                        <div class="small text-muted">{{ \Illuminate\Support\Str::limit($fee->notes, 60) }}</div>
                                    @endif
                                </td>
                                <td class="py-3">{{ number_format((float) $fee->amount, 2) }}</td>
                                <td class="py-3">{{ number_format($paidAmount, 2) }}</td>
                                <td class="py-3">
                                    {{ number_format(max(0, (float) $fee->amount - $paidAmount), 2) }}
                                </td>
                                <td class="py-3">{{ $fee->due_date?->format('Y-m-d') ?? 'N/A' }}</td>
                                <td class="py-3 text-end text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a href="{{ route('fees.edit', $fee) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           aria-label="Edit {{ $fee->title }} for {{ $fee->student?->first_name }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('fees.destroy', $fee) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    aria-label="Delete {{ $fee->title }}"
                                                    onclick="return confirm('Are you sure you want to delete this fee?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">No fees have been created yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 pt-3 border-top">{{ $fees->links() }}</div>
        </div>
    </div>
@endsection
