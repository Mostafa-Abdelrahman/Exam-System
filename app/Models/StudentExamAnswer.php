<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExamAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'exam_question_id',
        'written_answer',
        'graded',
    ];

      /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'graded' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function examQuestion()
    {
        return $this->belongsTo(ExamQuestion::class);
    }
}
