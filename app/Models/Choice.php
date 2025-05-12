<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'text',
        'is_correct',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_correct' => 'boolean',
    ];
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }
}
