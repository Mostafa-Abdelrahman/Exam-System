<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExamAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'exam_id',
        'start_time',
        'end_time',
        'submitted_at',
        'score',
        'feedback',
        'status',
    ];

      /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'submitted_at' => 'datetime',
        'score' => 'integer',
        ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function examQuestion()
    {
        return $this->belongsTo(ExamQuestion::class);
    }
    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
