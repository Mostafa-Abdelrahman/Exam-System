<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdminRoutesTest extends TestCase
{
    use RefreshDatabase;  // This ensures migrations are run before each test

    // To use the authenticated user for testing
    protected function actingAsAdmin()
    {
        $this->actingAs(\App\Models\User::factory()->create(['email' => 'admin@example.com', 'role' => 'admin']));
    }

    /** @test */
    public function it_can_access_the_admin_stats_page()
    {
        $this->actingAsAdmin();  // Act as an authenticated admin user

        $response = $this->get(route('admin.stats'));  // Make GET request to admin.stats route

        $response->assertStatus(200);  // Assert that the status code is 200 (OK)
    }

    /** @test */
    public function it_can_access_the_admin_exams_page()
    {
        $this->actingAsAdmin();  // Act as an authenticated admin user

        $response = $this->get(route('admin.exams'));  // Make GET request to admin.exams route

        $response->assertStatus(200);  // Assert that the status code is 200 (OK)
    }

    /** @test */
    public function it_can_create_a_course()
    {
        $this->actingAsAdmin();  // Act as an authenticated admin user

        $courseData = [
            'name' => 'New Course',
            'description' => 'Course description',
            'credits' => 3,
        ];

        $response = $this->post(route('admin.courses'), $courseData);  // Make POST request to admin.courses route

        $response->assertStatus(201);  // Assert that the status code is 201 (Created)
    }

    /** @test */
    public function it_can_show_a_user()
    {
        $this->actingAsAdmin();  // Act as an authenticated admin user

        $user = \App\Models\User::factory()->create();  // Create a user for testing

        $response = $this->get(route('admin.users.show', ['id' => $user->id]));  // Make GET request to admin.users.show route

        $response->assertStatus(200);  // Assert that the status code is 200 (OK)
        $response->assertJsonFragment(['id' => $user->id]);  // Assert that the correct user data is returned
    }

    /** @test */
    public function it_can_assign_a_doctor_to_a_course()
    {
        $this->actingAsAdmin();  // Act as an authenticated admin user

        $doctor = \App\Models\User::factory()->create(['role' => 'doctor']);
        $course = \App\Models\Course::factory()->create();

        $response = $this->post(route('admin.doctors.assign', ['doctorId' => $doctor->id, 'courseId' => $course->id]));

        $response->assertStatus(200);  // Assert that the status code is 200 (OK)
    }

    /** @test */
    public function it_can_enroll_a_student_in_a_course()
    {
        $this->actingAsAdmin();  // Act as an authenticated admin user

        $student = \App\Models\User::factory()->create(['role' => 'student']);
        $course = \App\Models\Course::factory()->create();

        $response = $this->post(route('admin.students.enroll', ['studentId' => $student->id, 'courseId' => $course->id]));

        $response->assertStatus(200);  // Assert that the status code is 200 (OK)
    }
}
