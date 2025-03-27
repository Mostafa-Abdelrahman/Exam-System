<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamQuestion>
 */
class ExamQuestionFactory extends Factory
{
    protected $model = ExamQuestion::class;

    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'question_id' => Question::factory(),
            'weight' => $this->faker->randomFloat(2, 0.5, 5.0), // Weight between 0.5 and 5.0
        ];
    }
}
