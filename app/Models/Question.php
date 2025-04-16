<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_text',
        'question_type_id',
        'chapter',
        'difficulty_level',
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
}
