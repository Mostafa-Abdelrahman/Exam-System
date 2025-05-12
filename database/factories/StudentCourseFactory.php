<?php

namespace Database\Factories;

use App\Models\StudentCourse;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentCourse>
 */
class StudentCourseFactory extends Factory
{
    protected $model = StudentCourse::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
        ];
    }
}
