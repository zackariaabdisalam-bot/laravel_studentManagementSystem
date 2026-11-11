@extends('layouts.app')

@section('title', 'Edit Enrollment')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Enrollment</h2>
            <p class="text-muted mb-0">Update the student course enrollment.</p>
        </div>
        <a href="{{ route('enrollments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Enrollments
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('enrollments.update', $enrollment) }}">
                @csrf
                @method('PUT')
                @include('enrollments._form')
            </form>
        </div>
    </div>
@endsection
