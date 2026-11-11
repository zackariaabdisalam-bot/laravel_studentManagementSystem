<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentEntryTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_record_an_offline_payment_against_a_fee_balance(): void
    {
        $user = User::factory()->create();
        $student = Student::query()->create([
            'student_number' => 'PAY-001',
            'first_name' => 'Jordan',
            'last_name' => 'Student',
            'status' => 'Active',
        ]);
        $fee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Tuition',
            'amount' => 500,
        ]);
        $paidFee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Already paid fee',
            'amount' => 100,
        ]);
        Payment::query()->create([
            'student_id' => $student->id,
            'fee_id' => $paidFee->id,
            'amount' => 100,
            'method' => 'cash',
            'status' => 'completed',
            'paid_at' => '2026-10-14',
        ]);

        $this->actingAs($user)
            ->get(route('payments.create'))
            ->assertOk()
            ->assertSee('name="fee_id"', false)
            ->assertSee('value="'.$fee->id.'"', false)
            ->assertSee('Balance: KSh 500.00')
            ->assertSee('id="amountWrapper"', false)
            ->assertSee('class="col-md-6 d-none"', false)
            ->assertSee('Enter the amount to request from this phone number.')
            ->assertDontSee('Already paid fee');

        $this->post(route('payments.store'), [
            'fee_id' => $fee->id,
            'amount' => '125.00',
            'paid_at' => '2026-10-15',
            'method' => 'bank',
            'reference' => 'BANK-REF-1001',
        ])->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'fee_id' => $fee->id,
            'amount' => '125.00',
            'method' => 'bank',
            'reference' => 'BANK-REF-1001',
            'status' => 'completed',
            'received_by' => $user->id,
        ]);

        $this->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Jordan Student')
            ->assertSee('BANK-REF-1001');
    }

    public function test_staff_cannot_manually_mark_an_m_pesa_receipt_as_paid(): void
    {
        $student = Student::query()->create([
            'student_number' => 'PAY-002',
            'first_name' => 'Casey',
            'last_name' => 'Student',
            'status' => 'Active',
        ]);
        $fee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Tuition',
            'amount' => 500,
        ]);

        $this->actingAs(User::factory()->create())
            ->from(route('payments.create'))
            ->post(route('payments.store'), [
                'fee_id' => $fee->id,
                'amount' => '100',
                'paid_at' => '2026-10-15',
                'method' => 'mpesa',
                'reference' => 'UNVERIFIED-RECEIPT',
                'phone' => '0712345678',
            ])
            ->assertRedirect(route('payments.create'))
            ->assertSessionHasErrors('method');

        $this->assertSame(0, Payment::query()->count());
    }
}
