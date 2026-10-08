<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_update_and_delete_courses(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Course Code')
            ->assertSee('Course Name')
            ->assertSee(route('courses.create'));

        $this->get(route('courses.create'))
            ->assertOk()
            ->assertSee('name="course_code"', false)
            ->assertSee('value="Active"', false);

        $this->post(route('courses.store'), [
            'course_code' => 'WEB-101',
            'course_name' => 'Web Development',
            'description' => 'Build modern web applications.',
            'duration' => 'One year',
            'status' => 'Active',
        ])->assertRedirect(route('courses.index'));

        $course = Course::query()->sole();
        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'course_code' => 'WEB-101',
            'course_name' => 'Web Development',
            'description' => 'Build modern web applications.',
            'status' => 'Active',
        ]);
        $this->get(route('courses.index'))
            ->assertOk()
            ->assertSee('WEB-101');

        $this->get(route('courses.index', ['edit' => $course->id]))
            ->assertOk()
            ->assertSee('Edit Course')
            ->assertSee('value="WEB-101"', false)
            ->assertSee('Build modern web applications.');

        $this->put(route('courses.update', $course), [
            'course_code' => 'WEB-101',
            'course_name' => 'Advanced Web Development',
            'duration' => 'Two years',
            'status' => 'Inactive',
            'description' => 'Build advanced web applications.',
        ])->assertRedirect(route('courses.index'));

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'course_code' => 'WEB-101',
            'course_name' => 'Advanced Web Development',
            'description' => 'Build advanced web applications.',
            'status' => 'Inactive',
        ]);

        $this->delete(route('courses.destroy', $course))
            ->assertRedirect(route('courses.index'));

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
    }
}
