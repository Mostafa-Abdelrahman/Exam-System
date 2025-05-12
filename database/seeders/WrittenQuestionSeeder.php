<?php

namespace Database\Seeders;

use App\Models\WrittenQuestion;
use Illuminate\Database\Seeder;

class WrittenQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WrittenQuestion::factory()->count(30)->create();
    }
}
