<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'major_id' => Major::inRandomOrder()->first()->id ?? null,
        ];
    }
}
