@extends('layouts.app')

@section('title', 'Courses')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Courses</h2>
            <p class="text-muted mb-0">Manage courses offered by the institution.</p>
        </div>
        <a href="{{ route('courses.create') }}" class="btn school-primary">
            <i class="bi bi-plus-circle me-1"></i>
            Add Course
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

    @if ($editing)
        <div class="card border-0 shadow-sm mb-4" id="course-form">
            <div class="card-body p-4">
                <h3 class="h5 mb-4">Edit Course</h3>
                <form action="{{ route('courses.update', $editing) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="course_code" class="form-label">Course Code</label>
                        <input type="text"
                               id="course_code"
                               name="course_code"
                               class="form-control @error('course_code') is-invalid @enderror"
                               value="{{ old('course_code', $editing->course_code) }}"
                               maxlength="50">
                        @error('course_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="course_name" class="form-label">Course Name</label>
                        <input type="text"
                               id="course_name"
                               name="course_name"
                               class="form-control @error('course_name') is-invalid @enderror"
                               value="{{ old('course_name', $editing->course_name) }}"
                               maxlength="255"
                               required>
                        @error('course_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="duration" class="form-label">Duration</label>
                        <input type="text"
                               id="duration"
                               name="duration"
                               class="form-control @error('duration') is-invalid @enderror"
                               value="{{ old('duration', $editing->duration) }}"
                               maxlength="100">
                        @error('duration')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="status" class="form-label">Status</label>
                        @php($selectedStatus = old('status', ucfirst(strtolower($editing->status))))
                        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="Active" @selected($selectedStatus === 'Active')>Active</option>
                            <option value="Inactive" @selected($selectedStatus === 'Inactive')>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description"
                                  name="description"
                                  rows="3"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $editing->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <a href="{{ route('courses.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn school-primary">update changes</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Course Code</th>
                            <th>Course Name</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($courses as $course)
                            <tr>
                                <td>{{ $courses->firstItem() + $loop->index }}</td>
                                <td><strong>{{ $course->course_code }}</strong></td>
                                <td>{{ $course->course_name }}</td>
                                <td>{{ $course->duration ?? 'N/A' }}</td>
                                <td>
                                    @if (strtolower($course->status) === 'active')
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('courses.index', ['edit' => $course->id]) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       aria-label="Edit {{ $course->course_name }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('courses.destroy', $course) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                aria-label="Delete {{ $course->course_name }}"
                                                onclick="return confirm('Are you sure you want to delete it?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No courses found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $courses->links() }}
            </div>
        </div>
    </div>
@endsection