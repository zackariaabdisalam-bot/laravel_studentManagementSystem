<?php

namespace App\Http\Controllers;

use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    // Display a paginated list of students.
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $students = Student::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('student_number', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('students.index', compact('students'));
    }

    // Show the form for creating a student.
    public function create(): View
    {
        return view('students.create');
    }

    // Validate and save a new student, then return to the student list.
    public function store(Request $request): RedirectResponse
    {
        $request->merge(['phone' => $this->normalizePhone($request->input('phone'))]);

        $validatedData = $request->validate([
            'student_number' => 'required|string|max:50|unique:students,student_number',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            'date_of_birth' => 'required|date',
            'phone' => ['nullable', 'string', 'regex:/^\+2547\d{8}$/'],
            'email' => 'required|email|max:255|unique:students,email',
            'address' => 'nullable|string',
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $validatedData['address'] = $validatedData['address'] ?? '';
        Student::query()->create($validatedData);

        return redirect()->route('students.index')
            ->with('success', 'Student added successfully.');
    }

    // Display a student's details.
    public function show(string $id)
    {
        //
    }

    // Show the form for editing an existing student.
    public function edit(Student $student): View
    {
        return view('students.edit', compact('student'));
    }

    // Validate and save changes to an existing student.
    public function update(Request $request, Student $student): RedirectResponse
    {
        $request->merge(['phone' => $this->normalizePhone($request->input('phone'))]);

        $validatedData = $request->validate([
            'student_number' => ['required', 'string', 'max:50', Rule::unique('students', 'student_number')->ignore($student)],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            'date_of_birth' => ['required', 'date'],
            'phone' => ['nullable', 'string', 'regex:/^\+2547\d{8}$/'],
            'email' => ['required', 'email', 'max:255', Rule::unique('students', 'email')->ignore($student)],
            'address' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $validatedData['address'] = $validatedData['address'] ?? '';
        $student->update($validatedData);

        return redirect()->route('students.index')
            ->with('success', 'Student updated successfully.');
    }

    // Delete a student only when no related records exist.
    public function destroy(Student $student): RedirectResponse
    {
        $hasRelatedRecords = (Schema::hasTable('fees') && $student->fees()->exists())
            || (Schema::hasTable('payments') && $student->payments()->exists())
            || (Schema::hasTable('enrollments') && $student->enrollments()->exists())
            || (Schema::hasTable('attendance') && $student->attendanceRecords()->exists());

        if ($hasRelatedRecords) {
            return redirect()->route('students.index')
                ->withErrors(['student' => 'Students with fee, payment, enrollment, or attendance records cannot be deleted.']);
        }

        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Student deleted successfully.');
    }

    private function normalizePhone(?string $phone): ?string
    {
        $phone = preg_replace('/[\s()-]/', '', trim((string) $phone));

        if ($phone === '') {
            return null;
        }

        if (preg_match('/^07\d{8}$/', $phone)) {
            return '+254'.substr($phone, 1);
        }

        if (preg_match('/^2547\d{8}$/', $phone)) {
            return '+'.$phone;
        }

        return $phone;
    }
}
