@extends('layouts.app')

@section('title', 'Add Fee')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Add Fee</h2>
            <p class="text-muted mb-0">Create a fee charge for a student.</p>
        </div>
        <a href="{{ route('fees.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Fees
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('fees.store') }}">
                @csrf
                @include('fees._form')
            </form>
        </div>
    </div>
@endsection
