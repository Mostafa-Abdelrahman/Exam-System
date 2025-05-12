<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'exam_id',
        'grade',
        'graded_at',
    ];

        /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'grade' => 'decimal:2',
        'graded_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }
       /**
     * Determine the letter grade based on the numeric grade.
     */
    public function getLetterGradeAttribute()
    {
        if ($this->grade >= 90) return 'A';
        if ($this->grade >= 80) return 'B';
        if ($this->grade >= 70) return 'C';
        if ($this->grade >= 60) return 'D';
        return 'F';
    }

    /**
     * Determine if this is a passing grade.
     */
    public function getIsPassingAttribute()
    {
        return $this->grade >= 60;
    }
}
