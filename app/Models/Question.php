<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'type',
        'chapter',
        'difficulty',
        'created_by',
        'evaluation_criteria',
    ];

    // Relationships
    public function questionType()
    {
        return $this->belongsTo(QuestionType::class);
    }
    /**
     * Get the choices for this question.
     */
    public function choice()
    {
        return $this->hasMany(Choice::class, 'question_id');
    }
    
    /**
     * Get the correct choice for this question (if it's a multiple choice question).
     */
    public function correctChoice()
    {
        return $this->choices()->where('is_correct', true)->first();
    }

    /**
     * Get the written question details.
     */
    public function writtenQuestion()
    {
        return $this->hasOne(WrittenQuestion::class, 'question_id');
    }

    /**
     * Check if this is a multiple choice question.
     */
    public function isMultipleChoice()
    {
        return $this->choices()->count() > 0;
    }

    /**
     * Check if this is a written question.
     */
    public function isWrittenQuestion()
    {
        return $this->writtenQuestion()->exists();
    }

    /**
     * Get the exam questions using this question.
     */
    public function examQuestion()
    {
        return $this->hasMany(ExamQuestion::class, 'question_id');
    }
    public function doctor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the choices for the question.
     */
    public function choices()
    {
        return $this->hasMany(Choice::class);
    }

    /**
     * Get the exams that include this question.
     */
    public function exams()
    {
        return $this->belongsToMany(Exam::class, 'exam_question')
            ->withPivot('weight');
    }

    /**
     * Get the answers for this question.
     */
    public function answers()
    {
        return $this->hasMany(Answer::class);
    }
}
