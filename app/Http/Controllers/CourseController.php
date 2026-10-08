<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()->orderByDesc('id')->paginate(10);
        $editing = $request->integer('edit')
            ? Course::query()->findOrFail($request->integer('edit'))
            : null;

        return view('courses.index', compact('courses', 'editing'));
    }

    public function create(): View
    {
        return view('courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Course::query()->create($request->validate([
            'course_code' => 'required|string|max:50|unique:courses,course_code',
            'course_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration' => 'nullable|string|max:100',
            'status' => 'required|string|max:50',
        ]));

        return redirect()->route('courses.index')->with('status', 'Course created successfully.');
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validatedData = $request->validate([
            'course_code' => ['required', 'string', 'max:50', Rule::unique('courses', 'course_code')->ignore($course)],
            'course_name' => ['required', 'string', 'max:255'],
            'duration' => ['nullable', 'string', 'max:100'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'description' => ['nullable', 'string'],
        ]);

        $course->update($validatedData);

        return redirect()->route('courses.index')->with('status', 'Course updated successfully.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        if (Schema::hasTable('enrollments') && $course->enrollments()->exists()) {
            return redirect()->route('courses.index')
                ->withErrors(['course' => 'Courses with enrolled students cannot be deleted.']);
        }

        $course->delete();

        return redirect()->route('courses.index')->with('status', 'Course deleted successfully.');
    }

}
