<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\ExamQuestion;
use App\Models\StudentExamAnswer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentExamAnswer>
 */
class StudentExamAnswerFactory extends Factory
{
    protected $model = StudentExamAnswer::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'exam_question_id' => ExamQuestion::factory(),
            'written_answer' => $this->faker->sentence(10),
            'graded' => $this->faker->boolean(),
        ];
    }
}
