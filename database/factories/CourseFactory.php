<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'course_name' => $this->faker->sentence(3),
            'course_code' => strtoupper($this->faker->unique()->bothify('??###')),
            'description' => $this->faker->paragraph(),
        ];
    }
}
