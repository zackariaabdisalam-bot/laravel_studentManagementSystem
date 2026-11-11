<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentPresentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_list_and_add_student_pages_render_the_professional_layout(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('students.index'))
            ->assertOk()
            ->assertSee('Student Directory')
            ->assertSee('No students yet')
            ->assertSee(route('students.create'));

        $this->get(route('students.create'))
            ->assertOk()
            ->assertSee('Student Details')
            ->assertSee('Contact Information')
            ->assertSee('Enrollment Status')
            ->assertSee('Add Student');
    }

    public function test_student_can_be_added_and_then_edited_from_the_student_directory(): void
    {
        $this->actingAs(User::factory()->create());
        $studentData = [
            'student_number' => 'ST-PRES-001',
            'first_name' => 'Taylor',
            'last_name' => 'Student',
            'gender' => 'Other',
            'date_of_birth' => '2007-05-12',
            'phone' => '0712345678',
            'email' => 'taylor.student@example.test',
            'address' => 'Nairobi',
            'status' => 'active',
        ];

        $this->post(route('students.store'), $studentData)
            ->assertRedirect(route('students.index'));

        $student = Student::query()->where('student_number', 'ST-PRES-001')->firstOrFail();
        $this->get(route('students.index'))
            ->assertOk()
            ->assertSee('Taylor Student')
            ->assertSee('ST-PRES-001')
            ->assertSee('Active');

        $this->get(route('students.edit', $student))
            ->assertOk()
            ->assertSee('Update this student’s profile')
            ->assertSee('Taylor');
    }
}
