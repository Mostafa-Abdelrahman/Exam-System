<?php

namespace Database\Seeders;

use App\Models\StudentExamAnswer;
use Illuminate\Database\Seeder;

class StudentExamAnswerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StudentExamAnswer::factory()->count(30)->create();
    }
}
