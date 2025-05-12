<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_exam_id',
        'question_id',
        'answer_text',
        'choice_id',
        'score',
        'feedback',
        'graded',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'integer',
        'graded' => 'boolean',
    ];

    /**
     * Get the student exam that owns the answer.
     */
    public function studentExam()
    {
        return $this->belongsTo(StudentExam::class);
    }

    /**
     * Get the question that owns the answer.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the choice that the student selected.
     */
    public function choice()
    {
        return $this->belongsTo(Choice::class);
    }
}