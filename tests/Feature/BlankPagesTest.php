<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlankPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_payments_and_reports_pages_render(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('payments.index'))
            ->assertOk()
            ->assertSee('Payments')
            ->assertSee('All Transactions')
            ->assertSee('Record Payment');

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertSee('Payment Report')
            ->assertSee('Detailed Transactions');
    }

    public function test_payments_and_reports_are_clickable_from_navigation(): void
    {
        $this->actingAs(User::factory()->create());
        $sidebar = view('partials.sidebar')->render();

        $this->assertStringContainsString('href="'.route('payments.index').'"', $sidebar);
        $this->assertStringContainsString('href="'.route('reports.index').'"', $sidebar);
    }
}
