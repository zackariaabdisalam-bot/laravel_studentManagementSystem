<div class="row g-3">
    <div class="col-md-6">
        <label for="student_id" class="form-label">Student</label>
        <select id="student_id" name="student_id" class="form-select @error('student_id') is-invalid @enderror" required>
            <option value="">Select a student</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}" @selected((string) old('student_id', $enrollment?->student_id) === (string) $student->id)>
                    {{ $student->student_number }} — {{ trim($student->first_name.' '.$student->last_name) }}
                </option>
            @endforeach
        </select>
        @error('student_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="course_id" class="form-label">Course</label>
        <select id="course_id" name="course_id" class="form-select @error('course_id') is-invalid @enderror" required>
            <option value="">Select a course</option>
            @foreach ($courses as $course)
                <option value="{{ $course->id }}" @selected((string) old('course_id', $enrollment?->course_id) === (string) $course->id)>
                    {{ $course->course_code }} — {{ $course->course_name }}
                </option>
            @endforeach
        </select>
        @error('course_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="semester" class="form-label">Semester</label>
        <select id="semester" name="semester" class="form-select @error('semester') is-invalid @enderror" required>
            <option value="">Select a semester</option>
            @foreach ($semesters as $semester)
                <option value="{{ $semester }}" @selected(old('semester', $enrollment?->semester?->name) === $semester)>
                    {{ $semester }}
                </option>
            @endforeach
        </select>
        @error('semester')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6">
        <label for="academic_year_date" class="form-label">Academic Year</label>
        @php($academicYearStart = $enrollment?->academicYear?->year ? substr($enrollment->academicYear->year, 0, 4).'-01-01' : '')
        <input id="academic_year_date"
               type="date"
               name="academic_year_date"
               value="{{ old('academic_year_date', $academicYearStart) }}"
               class="form-control @error('academic_year_date') is-invalid @enderror"
               required>
        <div class="form-text">Choose a date in the academic year. The year range is saved automatically.</div>
        @error('academic_year_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <a href="{{ route('enrollments.index') }}" class="btn btn-secondary">Cancel</a>
    <button type="submit" class="btn school-primary">
        <i class="bi bi-check-circle me-1"></i>
        {{ $enrollment ? 'Update Enrollment' : 'Save Enrollment' }}
    </button>
</div>
