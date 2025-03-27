<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            MajorSeeder::class,
            CourseSeeder::class,
            StudentSeeder::class,
            DoctorSeeder::class,
            StudentCourseSeeder::class,
            DoctorCourseSeeder::class,
            ExamSeeder::class,
            QuestionSeeder::class,
            ExamQuestionSeeder::class,
            StudentExamAnswerSeeder::class,
            WrittenQuestionSeeder::class,
            ChoiceSeeder::class,
            GradeSeeder::class,
        ]);
    }
}
