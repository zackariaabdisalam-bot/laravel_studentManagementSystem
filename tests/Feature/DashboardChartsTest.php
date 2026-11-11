<?php

namespace Tests\Feature;

use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardChartsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_charts_and_summary_use_completed_database_payments(): void
    {
        $student = Student::query()->create([
            'student_number' => 'DASH-001',
            'first_name' => 'Avery',
            'last_name' => 'Student',
            'status' => 'Active',
        ]);
        $fee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Tuition',
            'amount' => 500,
        ]);
        Payment::query()->create([
            'student_id' => $student->id,
            'fee_id' => $fee->id,
            'amount' => 120,
            'method' => 'mpesa',
            'status' => 'completed',
            'paid_at' => today()->toDateString(),
            'reference' => 'DASH-MPESA-001',
        ]);
        Payment::query()->create([
            'student_id' => $student->id,
            'fee_id' => $fee->id,
            'amount' => 200,
            'method' => 'cash',
            'status' => 'pending',
            'paid_at' => today()->toDateString(),
            'reference' => 'DASH-PENDING-001',
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="paymentTrendChart"', false)
            ->assertSee('id="paymentMethodsChart"', false)
            ->assertSee('id="courseEnrollmentsChart"', false)
            ->assertSee('id="studentStatusesChart"', false)
            ->assertSee('KSh 120.00')
            ->assertSee('KSh 380.00')
            ->assertSee('Avery Student')
            ->assertDontSee('DASH-PENDING-001')
            ->assertSee('M-Pesa')
            ->assertSee('Active');
    }
}
