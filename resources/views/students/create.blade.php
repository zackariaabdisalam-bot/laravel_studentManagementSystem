@extends('layouts.app')

@section('title', 'Add Student')

@section('content')
    <div class="container-fluid student-page">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <div class="small text-secondary mb-1"><a href="{{ route('students.index') }}" class="text-decoration-none">Students</a> / Add Student</div>
                <h1 class="h3 fw-bold mb-1">Add Student</h1>
                <p class="text-secondary mb-0">Create a student profile and add their contact information.</p>
            </div>
            <a href="{{ route('students.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Back to Students
            </a>
        </div>

        @include('students._form', ['student' => null])
    </div>
@endsection
