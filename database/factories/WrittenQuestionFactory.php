<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\WrittenQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WrittenQuestion>
 */
class WrittenQuestionFactory extends Factory
{
    protected $model = WrittenQuestion::class;

    public function definition(): array
    {
        return [
            'question_id' => Question::factory(),
            'evaluation_criteria' => $this->faker->paragraph(3),
        ];
    }
}
