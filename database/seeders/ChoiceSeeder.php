<?php

namespace Database\Seeders;

use App\Models\Choice;
use Illuminate\Database\Seeder;

class ChoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Choice::factory()->count(50)->create();
    }
}
