<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\SemesterYear;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollmentIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_enrollments_from_the_sidebar(): void
    {
        $student = Student::query()->create([
            'student_number' => 'ST-ENR-001',
            'first_name' => 'Taylor',
            'last_name' => 'Student',
        ]);
        $course = Course::query()->create([
            'course_code' => 'SCI-101',
            'course_name' => 'Science',
        ]);
        $semester = SemesterYear::query()->create(['name' => 'Semester 1']);
        $academicYear = AcademicYear::query()->create(['year' => '2026/2027']);

        Enrollment::query()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('enrollments.index'))
            ->assertOk()
            ->assertSee(route('enrollments.index'))
            ->assertSee('Enrollments')
            ->assertSee('Taylor Student')
            ->assertSee('SCI-101')
            ->assertSee('Science')
            ->assertSee('Semester 1')
            ->assertSee('2026/2027')
            ->assertSee('Add Enrollment')
            ->assertSee(route('enrollments.edit', 1));
    }

    public function test_authenticated_user_can_open_the_add_enrollment_form(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('enrollments.create'))
            ->assertOk()
            ->assertSee('Save Enrollment')
            ->assertSee('student_id')
            ->assertSee('course_id')
            ->assertSee('Semester 1')
            ->assertSee('Semester 2')
            ->assertSee('Semester 3')
            ->assertSee('academic_year_date');
    }

    public function test_enrollments_requires_authentication(): void
    {
        $this->get(route('enrollments.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_create_an_enrollment(): void
    {
        $student = Student::query()->create([
            'student_number' => 'ST-ENR-004',
            'first_name' => 'Alex',
            'last_name' => 'Student',
        ]);
        $course = Course::query()->create([
            'course_code' => 'BIO-101',
            'course_name' => 'Biology',
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('enrollments.store'), [
                'student_id' => $student->id,
                'course_id' => $course->id,
                'semester' => 'Semester 1',
                'academic_year_date' => '2026-09-01',
            ])
            ->assertRedirect(route('enrollments.index'))
            ->assertSessionHas('status', 'Enrollment added successfully.');

        $semester = SemesterYear::query()->where('name', 'Semester 1')->firstOrFail();
        $academicYear = AcademicYear::query()->where('year', '2026/2027')->firstOrFail();

        $this->assertDatabaseHas('enrollments', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'academic_year_id' => $academicYear->id,
        ]);
    }

    public function test_student_cannot_be_enrolled_in_the_same_course_twice_for_the_same_term(): void
    {
        $student = Student::query()->create([
            'student_number' => 'ST-ENR-005',
            'first_name' => 'Casey',
            'last_name' => 'Student',
        ]);
        $course = Course::query()->create([
            'course_code' => 'HIS-101',
            'course_name' => 'History',
        ]);
        $semester = SemesterYear::query()->create(['name' => 'Semester 1']);
        $academicYear = AcademicYear::query()->create(['year' => '2026/2027']);
        Enrollment::query()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->from(route('enrollments.index'))
            ->post(route('enrollments.store'), [
                'student_id' => $student->id,
                'course_id' => $course->id,
                'semester' => 'Semester 1',
                'academic_year_date' => '2026-09-01',
            ])
            ->assertRedirect(route('enrollments.index'))
            ->assertSessionHasErrors('student_id');

        $this->assertSame(1, Enrollment::query()->count());
    }

    public function test_authenticated_user_can_edit_an_enrollment(): void
    {
        $student = Student::query()->create([
            'student_number' => 'ST-ENR-006',
            'first_name' => 'Robin',
            'last_name' => 'Student',
        ]);
        $course = Course::query()->create([
            'course_code' => 'ART-101',
            'course_name' => 'Art',
        ]);
        $semester = SemesterYear::query()->create(['name' => 'Semester 1']);
        $academicYear = AcademicYear::query()->create(['year' => '2026/2027']);
        $enrollment = Enrollment::query()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('enrollments.edit', $enrollment))
            ->assertOk()
            ->assertSee('Edit Enrollment')
            ->assertSee('2026-01-01');

        $this->put(route('enrollments.update', $enrollment), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'semester' => 'Semester 2',
            'academic_year_date' => '2027-09-01',
        ])
            ->assertRedirect(route('enrollments.index'))
            ->assertSessionHas('status', 'Enrollment updated successfully.');

        $updatedSemester = SemesterYear::query()->where('name', 'Semester 2')->firstOrFail();
        $updatedAcademicYear = AcademicYear::query()->where('year', '2027/2028')->firstOrFail();

        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollment->id,
            'semester_id' => $updatedSemester->id,
            'academic_year_id' => $updatedAcademicYear->id,
        ]);
    }

    public function test_authenticated_user_can_remove_an_enrollment(): void
    {
        $student = Student::query()->create([
            'student_number' => 'ST-ENR-002',
            'first_name' => 'Morgan',
            'last_name' => 'Student',
        ]);
        $course = Course::query()->create([
            'course_code' => 'MAT-101',
            'course_name' => 'Mathematics',
        ]);
        $semester = SemesterYear::query()->create(['name' => 'Semester 2']);
        $academicYear = AcademicYear::query()->create(['year' => '2027/2028']);
        $enrollment = Enrollment::query()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->actingAs(User::factory()->create())
            ->from(route('enrollments.index'))
            ->delete(route('enrollments.destroy', $enrollment))
            ->assertRedirect(route('enrollments.index'))
            ->assertSessionHas('status', 'Enrollment removed successfully.');

        $this->assertDatabaseMissing('enrollments', ['id' => $enrollment->id]);
    }

    public function test_removing_an_enrollment_requires_authentication(): void
    {
        $student = Student::query()->create([
            'student_number' => 'ST-ENR-003',
            'first_name' => 'Jamie',
            'last_name' => 'Student',
        ]);
        $course = Course::query()->create([
            'course_code' => 'ENG-101',
            'course_name' => 'English',
        ]);
        $semester = SemesterYear::query()->create(['name' => 'Semester 3']);
        $academicYear = AcademicYear::query()->create(['year' => '2028/2029']);
        $enrollment = Enrollment::query()->create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'semester_id' => $semester->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->delete(route('enrollments.destroy', $enrollment))
            ->assertRedirect(route('login'));

        $this->assertDatabaseHas('enrollments', ['id' => $enrollment->id]);
    }
}
