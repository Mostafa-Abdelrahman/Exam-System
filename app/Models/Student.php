<?php

namespace App\Models; // Ensure this is correct

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'major_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id','id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id','id');
    }
    /**
     * Get the courses that this student is enrolled in.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'student_courses', 'student_id', 'course_id')
                    ->withPivot('student_course_id');
    }

    /**
     * Get the grades for this student.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'student_id');
    }

    /**
     * Get the exam answers for this student.
     */
    public function examAnswers()
    {
        return $this->hasMany(StudentExamAnswer::class, 'student_id');
    }
    
}
