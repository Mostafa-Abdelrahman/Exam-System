<?php

namespace Database\Factories;

use App\Models\QuestionType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuestionType>
 */
class QuestionTypeFactory extends Factory
{
    protected $model = QuestionType::class;

    public function definition(): array
    {
        return [
            'question_type' => $this->faker->word(),
        ];
    }
}
