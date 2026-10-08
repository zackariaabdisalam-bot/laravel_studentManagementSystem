<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentIndexRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_the_students_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('students.index'))
            ->assertOk()
            ->assertSee('Manage registered students.')
            ->assertSee(route('students.index'));
    }

    public function test_students_page_displays_existing_student_records(): void
    {
        $student = Student::query()->forceCreate([
            'student_number' => 'ST-100',
            'first_name' => 'Alex',
            'last_name' => 'Student',
            'address' => '',
            'status' => 'Active',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('students.index'))
            ->assertOk()
            ->assertSee('ST-100')
            ->assertSee('Alex')
            ->assertSee('Student')
            ->assertSee('Active')
            ->assertSee(route('students.show', $student))
            ->assertSee(route('students.edit', $student));
    }
}
