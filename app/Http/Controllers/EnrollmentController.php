<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\SemesterYear;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    private const SEMESTERS = ['Semester 1', 'Semester 2', 'Semester 3'];

    // Display a paginated list of enrollments with their related records.
    public function index(): View
    {
        $enrollments = Enrollment::query()
            ->with(['student', 'course', 'semester', 'academicYear'])
            ->orderByDesc('id')
            ->paginate(10);

        return view('enrollments.index', compact('enrollments'));
    }

    // Show the form for creating a new enrollment.
    public function create(): View
    {
        return view('enrollments.create', array_merge(
            $this->formOptions(),
            ['enrollment' => null],
        ));
    }

    // Show the form for editing an existing enrollment.
    public function edit(Enrollment $enrollment): View
    {
        $enrollment->load(['semester', 'academicYear']);

        return view('enrollments.edit', array_merge(
            $this->formOptions(),
            compact('enrollment'),
        ));
    }

    // Load the student, course, and semester choices used by enrollment forms.
    private function formOptions(): array
    {
        $students = Student::query()->orderBy('student_number')->get();
        $courses = Course::query()->orderBy('course_code')->get();
        $semesters = self::SEMESTERS;

        return compact('students', 'courses', 'semesters');
    }

    // Validate and create an enrollment, then return to the enrollment list.
    public function store(Request $request): RedirectResponse
    {
        $this->saveEnrollment($request);

        return redirect()->route('enrollments.index')->with('status', 'Enrollment added successfully.');
    }

    // Validate and update an existing enrollment, then return to the list.
    public function update(Request $request, Enrollment $enrollment): RedirectResponse
    {
        $this->saveEnrollment($request, $enrollment);

        return redirect()->route('enrollments.index')->with('status', 'Enrollment updated successfully.');
    }

    // Validate enrollment data, prevent duplicates, and save within a transaction.
    private function saveEnrollment(Request $request, ?Enrollment $enrollment = null): void
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'course_id' => 'required|integer|exists:courses,id',
            'semester' => 'required|string|in:Semester 1,Semester 2,Semester 3',
            'academic_year_date' => 'required|date_format:Y-m-d',
        ]);

        DB::transaction(function () use ($validated, $enrollment): void {
            $semester = SemesterYear::query()->firstOrCreate(['name' => $validated['semester']]);
            $startYear = (int) Carbon::createFromFormat('!Y-m-d', $validated['academic_year_date'])->format('Y');
            $academicYear = AcademicYear::query()->firstOrCreate([
                'year' => $startYear.'/'.($startYear + 1),
            ]);

            $enrollmentData = [
                'student_id' => $validated['student_id'],
                'course_id' => $validated['course_id'],
                'semester_id' => $semester->id,
                'academic_year_id' => $academicYear->id,
            ];

            $duplicate = Enrollment::query()
                ->where($enrollmentData)
                ->when($enrollment, fn ($query) => $query->where('id', '!=', $enrollment->getKey()))
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'student_id' => 'This student is already enrolled in this course for the selected semester and academic year.',
                ]);
            }

            if ($enrollment) {
                $enrollment->update($enrollmentData);
            } else {
                Enrollment::query()->create($enrollmentData);
            }
        });
    }

    // Delete an enrollment and return to the enrollment list.
    public function destroy(Enrollment $enrollment): RedirectResponse
    {
        $enrollment->delete();

        return redirect()->route('enrollments.index')->with('status', 'Enrollment removed successfully.');
    }
}
