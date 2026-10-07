<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManagementModulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_open_every_sidebar_module(): void
    {
        $user = User::factory()->create();

        foreach (['dashboard', 'students', 'courses', 'attendance', 'fees', 'payments', 'reports'] as $module) {
            $this->actingAs($user)
                ->get(route($module === 'dashboard' ? $module : $module.'.index'))
                ->assertOk();
        }
    }

    public function test_records_can_be_created_updated_and_reported(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('courses.store'), [
            'code' => 'SCI-101',
            'name' => 'Science',
            'duration' => 'One year',
        ])->assertRedirect(route('courses.index'));
        $course = Course::query()->firstOrFail();

        $this->post(route('students.store'), [
            'admission_number' => 'ST-001',
            'name' => 'Taylor Student',
            'email' => 'taylor@example.test',
            'course_id' => $course->id,
            'enrollment_date' => '2026-01-05',
            'status' => 'active',
        ])->assertRedirect(route('students.index'));
        $student = Student::query()->firstOrFail();

        $this->post(route('attendance.store'), [
            'student_id' => $student->id,
            'attendance_date' => '2026-02-01',
            'status' => 'present',
        ])->assertRedirect(route('attendance.index'));

        $this->post(route('fees.store'), [
            'student_id' => $student->id,
            'title' => 'Term one',
            'amount' => '1000.00',
        ])->assertRedirect(route('fees.index'));
        $fee = Fee::query()->firstOrFail();

        $this->post(route('payments.store'), [
            'fee_id' => $fee->id,
            'amount' => '300.00',
            'paid_at' => '2026-02-02',
            'method' => 'mobile_money',
            'reference' => 'PAY-001',
        ])->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas('payments', ['student_id' => $student->id, 'amount' => '300.00']);
        $this->assertEquals(700.0, $fee->fresh()->balance);
        $this->get(route('reports.index', ['from' => '2026-02-01', 'to' => '2026-02-28']))
            ->assertOk()
            ->assertSee('300.00');

        $this->put(route('students.update', $student->id), [
            'admission_number' => 'ST-001',
            'name' => 'Taylor Updated',
            'email' => 'taylor@example.test',
            'course_id' => $course->id,
            'enrollment_date' => '2026-01-05',
            'status' => 'active',
        ])->assertRedirect(route('students.index'));
        $this->assertDatabaseHas('students', ['id' => $student->id, 'name' => 'Taylor Updated']);
    }

    public function test_attendance_is_unique_per_student_per_day_and_payments_cannot_exceed_a_fee(): void
    {
        $this->actingAs(User::factory()->create());
        $student = Student::query()->create([
            'admission_number' => 'ST-002',
            'name' => 'Morgan Student',
            'enrollment_date' => '2026-01-01',
            'status' => 'active',
        ]);

        $this->post(route('attendance.store'), [
            'student_id' => $student->id,
            'attendance_date' => '2026-03-01',
            'status' => 'present',
        ])->assertRedirect(route('attendance.index'));

        $this->from(route('attendance.index'))->post(route('attendance.store'), [
            'student_id' => $student->id,
            'attendance_date' => '2026-03-01',
            'status' => 'absent',
        ])->assertSessionHasErrors('attendance_date');

        $fee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Term two',
            'amount' => 500,
        ]);

        $this->from(route('payments.index'))->post(route('payments.store'), [
            'fee_id' => $fee->id,
            'amount' => 501,
            'paid_at' => '2026-03-02',
            'method' => 'cash',
        ])->assertSessionHasErrors('amount');

        $this->assertSame(0, Payment::query()->count());
    }

    public function test_sidebar_links_point_to_named_module_routes(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Student Management System')
            ->assertSee(route('courses.index'))
            ->assertSee(route('attendance.index'))
            ->assertSee(route('fees.index'))
            ->assertSee(route('payments.index'))
            ->assertSee(route('reports.index'));
    }

    public function test_dashboard_displays_controller_provided_summary_values(): void
    {
        $course = Course::query()->create([
            'code' => 'BUS-101',
            'name' => 'Business Studies',
            'duration' => 'One year',
        ]);
        $student = Student::query()->create([
            'admission_number' => 'ST-DASH-001',
            'name' => 'Jordan Student',
            'course_id' => $course->id,
            'enrollment_date' => today()->toDateString(),
            'status' => 'active',
        ]);
        $fee = Fee::query()->create([
            'student_id' => $student->id,
            'title' => 'Term one',
            'amount' => 2000,
        ]);
        Payment::query()->create([
            'fee_id' => $fee->id,
            'student_id' => $student->id,
            'amount' => 700,
            'paid_at' => today()->toDateString(),
            'method' => 'mobile_money',
        ]);
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1')
            ->assertSee('ksh 700.00')
            ->assertSee('ksh 1,300.00')
            ->assertSee('Jordan Student')
            ->assertSee('Business Studies')
            ->assertSee('Recent payments')
            ->assertSee('Welcome to the Student Management System')
            ->assertDontSee('attendance records today');
    }

    public function test_dashboard_requires_authentication(): void
    {
        $this->get(route('dashboard'))
            ->assertRedirect(route('login'));
    }
}
