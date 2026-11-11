@extends('layouts.app')

@section('title', 'Edit Fee')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Fee</h2>
            <p class="text-muted mb-0">Update this student's fee charge.</p>
        </div>
        <a href="{{ route('fees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Fees
        </a>
    </div>

    @if (($fee->payments_sum_amount ?? 0) > 0)
        <div class="alert alert-info" role="status">
            This fee has payments. Its student cannot be changed and its amount cannot be reduced below the amount already paid.
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('fees.update', $fee) }}">
                @csrf
                @method('PUT')
                @include('fees._form')
            </form>
        </div>
    </div>
@endsection
