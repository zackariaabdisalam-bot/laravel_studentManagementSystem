<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_student(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('students.store'), [
                'student_number' => 'ST-101',
                'first_name' => 'Alex',
                'last_name' => 'Student',
                'gender' => 'Other',
                'date_of_birth' => '2005-06-01',
                'phone' => '0712345678',
                'email' => 'alex@example.test',
                'address' => null,
                'status' => 'active',
            ])
            ->assertRedirect(route('students.index'))
            ->assertSessionHas('success', 'Student added successfully.');

        $this->assertDatabaseHas('students', [
            'student_number' => 'ST-101',
            'first_name' => 'Alex',
            'last_name' => 'Student',
            'gender' => 'Other',
            'date_of_birth' => '2005-06-01',
            'phone' => '+254712345678',
            'address' => '',
            'status' => 'active',
        ]);
    }

    public function test_student_can_be_edited_and_deleted(): void
    {
        $student = Student::query()->forceCreate([
            'student_number' => 'ST-102',
            'first_name' => 'Sam',
            'last_name' => 'Learner',
            'gender' => 'Other',
            'date_of_birth' => '2004-04-12',
            'phone' => '0712345678',
            'email' => 'sam@example.test',
            'address' => '',
            'status' => 'active',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('students.edit', $student))
            ->assertOk()
            ->assertSee('Edit Student')
            ->assertSee('value="ST-102"', false)
            ->assertSee('value="2004-04-12"', false);

        $this->put(route('students.update', $student), [
            'student_number' => 'ST-102',
            'first_name' => 'Samuel',
            'last_name' => 'Learner',
            'gender' => 'Other',
            'date_of_birth' => '2004-04-12',
            'phone' => '0712345678',
            'email' => 'sam@example.test',
            'address' => '',
            'status' => 'inactive',
        ])
            ->assertRedirect(route('students.index'))
            ->assertSessionHas('success', 'Student updated successfully.');

        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Samuel',
            'phone' => '+254712345678',
            'status' => 'inactive',
        ]);

        $this->delete(route('students.destroy', $student))
            ->assertRedirect(route('students.index'))
            ->assertSessionHas('success', 'Student deleted successfully.');

        $this->assertDatabaseMissing('students', ['id' => $student->id]);
    }

    public function test_student_with_fee_records_cannot_be_deleted(): void
    {
        $student = Student::query()->forceCreate([
            'student_number' => 'ST-103',
            'first_name' => 'Taylor',
            'last_name' => 'Learner',
            'address' => '',
            'status' => 'active',
        ]);
        $student->fees()->create(['title' => 'Term fee', 'amount' => 100]);

        $this->actingAs(User::factory()->create())
            ->delete(route('students.destroy', $student))
            ->assertRedirect(route('students.index'))
            ->assertSessionHasErrors('student');

        $this->assertDatabaseHas('students', ['id' => $student->id]);
    }

    public function test_student_phone_must_be_a_kenyan_mobile_number(): void
    {
        $this->actingAs(User::factory()->create())
            ->from(route('students.create'))
            ->post(route('students.store'), [
                'student_number' => 'ST-INVALID-PHONE',
                'first_name' => 'Alex',
                'last_name' => 'Student',
                'gender' => 'Other',
                'date_of_birth' => '2005-06-01',
                'phone' => '1234567890',
                'email' => 'alex.invalid@example.test',
                'address' => null,
                'status' => 'active',
            ])
            ->assertSessionHasErrors('phone');
    }
}
