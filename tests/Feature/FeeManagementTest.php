<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_create_edit_and_delete_a_student_fee(): void
    {
        $this->actingAs(User::factory()->create());
        $student = $this->createStudent('ST-FEE-101');

        $this->get(route('fees.index'))
            ->assertOk()
            ->assertSee('Add Fee');

        $this->get(route('fees.create'))
            ->assertOk()
            ->assertSee('>Semester</label>', false)
            ->assertSee('ST-FEE-101')
            ->assertSee('Semester 1')
            ->assertSee('Semester 2')
            ->assertSee('Semester 3');

        $this->post(route('fees.store'), [
            'student_id' => $student->id,
            'title' => 'Semester 1',
            'amount' => '1200.00',
            'due_date' => '2026-11-01',
            'notes' => 'Semester tuition',
        ])
            ->assertRedirect(route('fees.index'))
            ->assertSessionHas('success', 'Fee created successfully.');

        $fee = Fee::query()->firstOrFail();
        $this->assertDatabaseHas('fees', [
            'id' => $fee->id,
            'student_id' => $student->id,
            'title' => 'Semester 1',
            'amount' => '1200.00',
        ]);

        $this->get(route('fees.edit', $fee))
            ->assertOk()
            ->assertSee('ST-FEE-101');

        $this->put(route('fees.update', $fee), [
            'student_id' => $student->id,
            'title' => 'Semester 2',
            'amount' => '1300.00',
            'due_date' => '2026-11-15',
            'notes' => 'Updated notes',
        ])
            ->assertRedirect(route('fees.index'))
            ->assertSessionHas('success', 'Fee updated successfully.');

        $this->assertDatabaseHas('fees', [
            'id' => $fee->id,
            'title' => 'Semester 2',
            'amount' => '1300.00',
        ]);

        $this->delete(route('fees.destroy', $fee))
            ->assertRedirect(route('fees.index'))
            ->assertSessionHas('success', 'Fee deleted successfully.');

        $this->assertDatabaseMissing('fees', ['id' => $fee->id]);
    }

    public function test_fee_name_must_be_one_of_the_three_semesters(): void
    {
        $this->actingAs(User::factory()->create());
        $student = $this->createStudent('ST-FEE-104');

        $this->from(route('fees.create'))
            ->post(route('fees.store'), [
                'student_id' => $student->id,
                'title' => 'Tuition',
                'amount' => '1200.00',
            ])
            ->assertRedirect(route('fees.create'))
            ->assertSessionHasErrors('title');

        $this->assertSame(0, Fee::query()->count());
    }

    public function test_fee_with_payments_cannot_be_deleted_or_reassigned_or_reduced_below_paid_amount(): void
    {
        $this->actingAs(User::factory()->create());
        $student = $this->createStudent('ST-FEE-102');
        $otherStudent = $this->createStudent('ST-FEE-103');
        $fee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Tuition',
            'amount' => 100,
        ]);
        Payment::query()->create([
            'fee_id' => $fee->id,
            'student_id' => $student->id,
            'amount' => 40,
            'method' => 'cash',
            'status' => 'completed',
            'paid_at' => '2026-10-09',
        ]);

        $this->delete(route('fees.destroy', $fee))
            ->assertRedirect(route('fees.index'))
            ->assertSessionHasErrors('fee');

        $this->put(route('fees.update', $fee), [
            'student_id' => $otherStudent->id,
            'title' => 'Tuition',
            'amount' => 100,
        ])->assertSessionHasErrors('student_id');

        $this->put(route('fees.update', $fee), [
            'student_id' => $student->id,
            'title' => 'Tuition',
            'amount' => 39,
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('fees', [
            'id' => $fee->id,
            'student_id' => $student->id,
            'amount' => '100.00',
        ]);
    }

    private function createStudent(string $studentNumber): Student
    {
        return Student::query()->create([
            'student_number' => $studentNumber,
            'first_name' => 'Fee',
            'last_name' => 'Student',
            'gender' => 'Other',
            'date_of_birth' => '2005-06-01',
            'email' => strtolower($studentNumber).'@example.test',
            'status' => 'active',
        ]);
    }
}
