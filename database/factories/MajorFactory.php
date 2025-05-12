<?php

namespace Database\Factories;

use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Major>
 */
class MajorFactory extends Factory
{
    protected $model = Major::class;

    public function definition(): array
    {
        return [
            'major_name' => $this->faker->unique()->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
