<?php

namespace Database\Factories;

use App\Models\Grade;
use App\Models\Student;
use App\Models\Exam;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    protected $model = Grade::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'exam_id' => Exam::factory(),
            'grade' => $this->faker->randomFloat(2, 50, 100), // Random grade between 50 and 100
            'graded_at' => Carbon::now(),
        ];
    }
}
