@extends('layouts.app')
@section('title', 'Add Student')
@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h2 class="fw-bold">Add Student</h2>
        <p class="text-muted">
            Register a new student.
        </p>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form method="POST"
                  action="{{ route('students.store') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            Student Number
                        </label>
                        <input type="text"
                               name="student_number"
                               value="{{ old('student_number') }}"
                               class="form-control @error('student_number') is-invalid @enderror">
                        @error('student_number')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            First Name
                        </label>
                        <input type="text"
                               name="first_name"
                               value="{{ old('first_name') }}"
                               class="form-control @error('first_name') is-invalid @enderror">
                        @error('first_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            Last Name
                        </label>
                        <input type="text"
                               name="last_name"
                               value="{{ old('last_name') }}"
                               class="form-control @error('last_name') is-invalid @enderror">
                        @error('last_name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            Gender
                        </label>
                        <select name="gender"
                                class="form-select @error('gender') is-invalid @enderror">
                            <option value="">Select Gender</option>
                            <option value="Male"
                                {{ old('gender') === 'Male' ? 'selected' : '' }}>
                                Male
                            </option>
                            <option value="Female"
                                {{ old('gender') === 'Female' ? 'selected' : '' }}>
                                Female
                            </option>
                            <option value="Other"
                                {{ old('gender') === 'Other' ? 'selected' : '' }}>
                                Other
                            </option>
                        </select>
                        @error('gender')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            Date of Birth
                        </label>
                        <input type="date"
                               name="date_of_birth"
                               value="{{ old('date_of_birth') }}"
                               class="form-control @error('date_of_birth') is-invalid @enderror">
                        @error('date_of_birth')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            Phone
                        </label>
                        <input type="text"
                               name="phone"
                               value="{{ old('phone') }}"
                               class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            Email
                        </label>
                        <input type="email"
                               name="email"
                               value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">
                            Status
                        </label>
                        <select name="status"
                                class="form-select @error('status') is-invalid @enderror">
                            <option value="active"
                                {{ old('status', 'active') === 'active' ? 'selected' : '' }}>
                                Active
                            </option>
                            <option value="inactive"
                                {{ old('status') === 'inactive' ? 'selected' : '' }}>
                                Inactive
                            </option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">
                            Address
                        </label>
                        <textarea name="address"
                                  rows="3"
                                  class="form-control @error('address') is-invalid @enderror">{{ old('address') }}</textarea>
                        @error('address')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
                <div class="mt-4">
                    <a href="{{ route('students.index') }}"
                       class="btn btn-secondary">
                        Cancel
                    </a>
                    <button type="submit"
                            class="btn school-primary">
                        <i class="bi bi-check-lg me-1"></i>
                        Save Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection