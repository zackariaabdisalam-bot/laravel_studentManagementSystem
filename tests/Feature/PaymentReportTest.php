<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_report_contains_no_unrecorded_transactions(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Payment Report')
            ->assertSee('View and analyze completed student payments')
            ->assertSee('Total Collected')
            ->assertSee('KSh 0.00')
            ->assertSee('0 payments')
            ->assertSee('Fee Structure');
    }

    public function test_report_filters_real_transactions_by_method_and_date_range(): void
    {
        $this->createPayment('2026-10-15', 'mpesa', 100, 'MPESA-REAL-01');
        $this->createPayment('2026-10-14', 'cash', 1000, 'CASH-REAL-01');

        $this->actingAs(User::factory()->create())
            ->get(route('reports.index', [
                'from' => '2026-10-01',
                'to' => '2026-10-15',
                'method' => 'mpesa',
            ]))
            ->assertOk()
            ->assertSee('KSh 100.00')
            ->assertSee('1 payments')
            ->assertSee('MPESA-REAL-01')
            ->assertDontSee('CASH-REAL-01');
    }

    public function test_report_exports_real_filtered_transactions_as_csv(): void
    {
        $this->createPayment('2026-10-10', 'bank', 100, 'BANK-REAL-01');

        $response = $this->actingAs(User::factory()->create())
            ->get(route('reports.index', ['method' => 'bank', 'export' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertHeader('content-disposition', 'attachment; filename=payment-report.csv');

        $csvLines = preg_split('/\r\n|\n|\r/', trim($response->streamedContent()));
        $this->assertSame(
            ['Student 1', 'RCP-000001', 'Examination Fee', '100.00', 'Bank', 'BANK-REAL-01', '2026-10-10', 'Completed'],
            str_getcsv($csvLines[1]),
        );
    }

    public function test_payments_page_shows_only_saved_transactions(): void
    {
        $this->createPayment('2026-10-15', 'mpesa', 100, 'MPESA-REAL-01');

        $this->actingAs(User::factory()->create())
            ->get(route('payments.index'))
            ->assertOk()
            ->assertSee('1 payments')
            ->assertSee('MPESA-REAL-01')
            ->assertDontSee('Grace Wanjiku')
            ->assertDontSee('RCP-2026-001');
    }

    public function test_staff_recorded_payment_is_saved_and_visible_in_the_payment_views(): void
    {
        $student = $this->createStudent();
        $fee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Tuition',
            'amount' => 500,
        ]);
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('payments.create'))
            ->assertOk()
            ->assertSee('M-Pesa')
            ->assertSee('Cash')
            ->assertSee('Bank')
            ->assertSee('Cheque')
            ->assertSee('M-Pesa STK Push')
            ->assertSee('verified Safaricom callback')
            ->assertSee('Cash Receipt Number')
            ->assertSee('Bank Reference / Transaction ID')
            ->assertSee('Cheque Number');

        $this->post(route('payments.store'), [
            'fee_id' => $fee->id,
            'amount' => '100.00',
            'paid_at' => '2026-10-15',
            'method' => 'cash',
            'reference' => 'CASH-REAL-02',
        ])->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas('payments', [
            'student_id' => $student->id,
            'fee_id' => $fee->id,
            'method' => 'cash',
            'reference' => 'CASH-REAL-02',
            'received_by' => $user->id,
            'status' => 'completed',
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('KSh 100.00')
            ->assertSee('1 payments')
            ->assertSee('CASH-REAL-02');

        $this->get(route('payments.index'))
            ->assertOk()
            ->assertSee('1 payments')
            ->assertSee('Student 1')
            ->assertSee('CASH-REAL-02')
            ->assertSee('KSh 100.00');
    }

    public function test_offline_payment_methods_update_only_their_own_report_totals(): void
    {
        $student = $this->createStudent();
        $fee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Tuition',
            'amount' => 500,
        ]);
        $this->actingAs(User::factory()->create());

        foreach ([
            'cash' => ['CASH-REAL-03', 10],
            'bank' => ['BANK-REAL-02', 20],
            'cheque' => ['CHEQUE-REAL-01', 30],
        ] as $method => [$reference, $amount]) {
            $this->post(route('payments.store'), [
                'fee_id' => $fee->id,
                'amount' => (string) $amount,
                'paid_at' => '2026-10-15',
                'method' => $method,
                'reference' => $reference,
            ])->assertRedirect(route('payments.index'));

            $this->assertDatabaseHas('payments', [
                'method' => $method,
                'reference' => $reference,
            ]);
        }

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('KSh 60.00')
            ->assertSee('KSh 10.00')
            ->assertSee('KSh 20.00')
            ->assertSee('KSh 30.00')
            ->assertSee('3 payments');
    }

    private function createPayment(string $date, string $method, float $amount, string $reference): Payment
    {
        $student = $this->createStudent();
        $fee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Examination Fee',
            'amount' => $amount,
        ]);

        return Payment::query()->create([
            'student_id' => $student->id,
            'fee_id' => $fee->id,
            'amount' => $amount,
            'method' => $method,
            'reference' => $reference,
            'paid_at' => $date,
            'status' => 'completed',
        ]);
    }

    private function createStudent(): Student
    {
        $number = Student::query()->count() + 1;

        return Student::query()->create([
            'student_number' => 'PAY-'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
            'first_name' => 'Student',
            'last_name' => (string) $number,
            'status' => 'Active',
        ]);
    }
}
