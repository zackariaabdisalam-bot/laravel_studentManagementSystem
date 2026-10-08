<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlankPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_requested_management_and_profile_pages_render_blank(): void
    {
        $this->actingAs(User::factory()->create());

        foreach (['students', 'attendance', 'fees', 'payments', 'reports'] as $module) {
            $this->get(route($module.'.index'))
                ->assertOk()
                ->assertSee('<body></body>', false)
                ->assertDontSee('Student Management System');
        }

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('<body></body>', false)
            ->assertDontSee('Profile Information');
    }

    public function test_blank_sections_are_not_clickable_from_navigation_or_dashboard(): void
    {
        $this->actingAs(User::factory()->create());
        $sidebar = view('partials.sidebar')->render();
        $navbar = view('partials.navbar')->render();

        foreach (['students', 'attendance', 'fees', 'payments', 'reports'] as $module) {
            $this->assertStringNotContainsString('href="'.route($module.'.index').'"', $sidebar);
        }

        $this->assertStringNotContainsString('href="'.route('profile.edit').'"', $sidebar);
        $this->assertStringNotContainsString('href="'.route('profile.edit').'"', $navbar);
        $this->assertStringContainsString('aria-disabled="true"', $sidebar);
        $this->assertStringContainsString('aria-disabled="true"', $navbar);
    }
}
