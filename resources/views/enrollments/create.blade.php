@extends('layouts.app')

@section('title', 'Add Enrollment')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Add Enrollment</h2>
            <p class="text-muted mb-0">Enroll a student in a course.</p>
        </div>
        <a href="{{ route('enrollments.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Enrollments
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('enrollments.store') }}">
                @csrf
                @include('enrollments._form')
            </form>
        </div>
    </div>
@endsection
