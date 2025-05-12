<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Major;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doctor>
 */
class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'major_id' => Major::inRandomOrder()->first()->id ?? null,
            'specialization' => $this->faker->word(),
        ];
    }
}
