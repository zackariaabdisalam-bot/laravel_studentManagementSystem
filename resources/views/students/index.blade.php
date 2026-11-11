@extends('layouts.app')

@section('title', 'Student Directory')

@push('styles')
    <style>
        .student-directory-card {
            border: 0;
            box-shadow: 0 4px 18px rgb(15 23 42 / 5%);
            overflow: hidden;
        }

        .student-directory-table {
            min-width: 980px;
        }

        .student-directory-table thead th {
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.045em;
            text-transform: uppercase;
            white-space: nowrap;
            padding: 0.95rem 0.85rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .student-directory-table tbody td {
            padding: 0.9rem 0.85rem;
            border-color: #edf0f5;
        }

        .student-directory-table tbody tr:hover {
            background: #f8fafc;
        }

        .student-directory-search {
            max-width: 420px;
            width: 100%;
        }

        .student-directory-search .form-control,
        .student-directory-search .input-group-text {
            background: #f8fafc;
        }

        .student-list-avatar {
            width: 42px;
            height: 42px;
            min-width: 42px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #e8efff;
            color: #3157c8;
            font-weight: 700;
        }

        .student-action {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.6rem;
        }

        .student-table-email {
            max-width: 230px;
            overflow-wrap: anywhere;
        }

        @media (max-width: 575.98px) {
            .student-directory-search {
                max-width: none;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid px-0">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
            <div>
                <p class="text-uppercase text-primary fw-semibold small mb-1">Student records</p>
                <h1 class="h2 fw-bold mb-1">Student Directory</h1>
                <p class="text-secondary mb-0">Manage registered students.</p>
            </div>
            <a href="{{ route('students.create') }}" class="btn btn-primary px-4">
                <i class="bi bi-person-plus me-1" aria-hidden="true"></i>
                Add Student
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="status">
                <i class="bi bi-check-circle-fill me-2" aria-hidden="true"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->has('student'))
            <div class="alert alert-danger" role="alert">{{ $errors->first('student') }}</div>
        @endif

        <section class="student-directory-card card mb-4" aria-label="Student list">
            <div class="card-header bg-white border-0 p-3 p-lg-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                    <div>
                        <h2 class="h5 fw-bold mb-1">All Students</h2>
                        <p class="small text-secondary mb-0">
                            Showing {{ $students->firstItem() ?? 0 }}–{{ $students->lastItem() ?? 0 }}
                            of {{ $students->total() }} {{ \Illuminate\Support\Str::plural('student', $students->total()) }}
                        </p>
                    </div>

                    <form action="{{ route('students.index') }}" method="GET" role="search" class="student-directory-search">
                        <label for="student-search" class="visually-hidden">Search students</label>
                        <div class="input-group">
                            <span class="input-group-text border-end-0">
                                <i class="bi bi-search text-secondary" aria-hidden="true"></i>
                            </span>
                            <input id="student-search" type="search" name="search"
                                   class="form-control border-start-0"
                                   placeholder="Name, admission number, phone or email"
                                   value="{{ request('search') }}">
                            @if (request()->filled('search'))
                                <a href="{{ route('students.index') }}" class="btn btn-light border" aria-label="Clear search" title="Clear search">
                                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                                </a>
                            @endif
                            <button class="btn btn-primary px-3" type="submit">Search</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 student-directory-table">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Student</th>
                            <th>Admission No.</th>
                            <th>Gender</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th class="text-center pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            @php
                                $status = strtolower((string) $student->status);
                                $statusClass = match ($status) {
                                    'active' => 'success',
                                    'inactive' => 'secondary',
                                    default => 'warning',
                                };
                                $initials = mb_strtoupper(
                                    mb_substr((string) $student->first_name, 0, 1)
                                    .mb_substr((string) $student->last_name, 0, 1)
                                );
                            @endphp
                            <tr>
                                <td class="ps-4 text-secondary">{{ $students->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="student-list-avatar" aria-hidden="true">{{ $initials ?: 'S' }}</span>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $student->first_name }} {{ $student->last_name }}</div>
                                            <div class="small text-secondary">Student</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-light text-dark border px-3 py-2">
                                        {{ $student->student_number }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $genderClass = match (strtolower((string) $student->gender)) {
                                            'female' => 'danger',
                                            'male' => 'primary',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge rounded-pill bg-{{ $genderClass }}-subtle text-{{ $genderClass }}-emphasis px-3 py-2">
                                        {{ $student->gender ?: 'Not specified' }}
                                    </span>
                                </td>
                                <td class="text-secondary">{{ $student->phone ?: 'Not provided' }}</td>
                                <td class="student-table-email text-secondary">{{ $student->email ?: 'Not provided' }}</td>
                                <td>
                                    <span class="badge rounded-pill bg-{{ $statusClass }}-subtle text-{{ $statusClass }}-emphasis px-3 py-2">
                                        <i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;" aria-hidden="true"></i>
                                        {{ ucfirst($status ?: 'unknown') }}
                                    </span>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="d-inline-flex justify-content-center gap-2">
                                        <a href="{{ route('students.show', $student) }}"
                                           class="btn btn-light border student-action text-primary"
                                           aria-label="View {{ $student->first_name }} {{ $student->last_name }}"
                                           title="View student">
                                            <i class="bi bi-eye" aria-hidden="true"></i>
                                        </a>
                                        <a href="{{ route('students.edit', $student) }}"
                                           class="btn btn-light border student-action text-secondary"
                                           aria-label="Edit {{ $student->first_name }} {{ $student->last_name }}"
                                           title="Edit student">
                                            <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary mb-3"
                                          style="width: 58px; height: 58px;" aria-hidden="true">
                                        <i class="bi bi-people fs-3"></i>
                                    </span>
                                    <h3 class="h5 fw-bold mb-1">{{ request()->filled('search') ? 'No matching students' : 'No students yet' }}</h3>
                                    <p class="text-secondary mb-3">
                                        {{ request()->filled('search') ? 'Try a different search term.' : 'Add a student to get started.' }}
                                    </p>
                                    @if (request()->filled('search'))
                                        <a href="{{ route('students.index') }}" class="btn btn-outline-primary btn-sm">Clear search</a>
                                    @else
                                        <a href="{{ route('students.create') }}" class="btn btn-primary btn-sm">
                                            <i class="bi bi-person-plus me-1" aria-hidden="true"></i>Add Student
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($students->hasPages())
                <div class="card-footer bg-white border-0 px-3 px-lg-4 py-3">
                    {{ $students->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </section>
    </div>
@endsection
