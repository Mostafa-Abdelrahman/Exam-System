<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WrittenQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'evaluation_criteria',
    ];

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
