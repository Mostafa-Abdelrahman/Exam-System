<?php

namespace Database\Factories;

use App\Models\Question;
use App\Models\QuestionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition(): array
    {
        return [
            'question_text' => $this->faker->sentence(),
            'question_type_id' => QuestionType::factory(),
            'chapter' => $this->faker->word(),
            'difficulty_level' => $this->faker->randomElement(['easy', 'medium', 'hard']),
        ];
    }
}
