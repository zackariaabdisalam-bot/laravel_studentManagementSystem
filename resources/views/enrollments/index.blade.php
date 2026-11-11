@extends('layouts.app')

@section('title', 'Enrollments')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Enrollments</h2>
            <p class="text-muted mb-0">Manage student course enrollments.</p>
        </div>
        <a href="{{ route('enrollments.create') }}" class="btn school-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Add Enrollment
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success" role="alert">{{ session('status') }}</div>
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
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student No.</th>
                            <th>Student Name</th>
                            <th>Course</th>
                            <th>Semester</th>
                            <th>Academic Year</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($enrollments as $enrollment)
                            <tr>
                                <td>{{ $enrollments->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $enrollment->student->student_number }}</strong></td>
                                <td>{{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}</td>
                                <td>{{ $enrollment->course->course_code }} — {{ $enrollment->course->course_name }}</td>
                                <td>{{ $enrollment->semester->name }}</td>
                                <td>{{ $enrollment->academicYear->year }}</td>
                                <td class="text-end text-nowrap">
                                    <div class="d-inline-flex align-items-center gap-2">
                                    <a href="{{ route('enrollments.edit', $enrollment) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       aria-label="Edit enrollment for {{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('enrollments.destroy', $enrollment) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Remove this student from the course?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                aria-label="Delete enrollment for {{ $enrollment->student->first_name }} {{ $enrollment->student->last_name }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No enrollments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3 pt-3 border-top">{{ $enrollments->links() }}</div>
        </div>
    </div>
@endsection
