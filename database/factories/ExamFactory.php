<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    protected $model = Exam::class;

    public function definition(): array
    {
        return [
            'exam_name' => $this->faker->sentence(3),
            'course_id' => Course::factory(),
            'exam_date' => Carbon::now()->addDays(rand(1, 30)),
            'exam_duration' => rand(30, 180), // Duration in minutes
            'created_by' => User::factory(),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
        ];
    }
}
