<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_type',
    ];

    
    /**
     * Get the questions of this type.
     */
    public function question()
    {
        return $this->hasMany(Question::class, 'question_type_id');
    }
}
