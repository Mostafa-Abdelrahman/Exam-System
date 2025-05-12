<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * Get the majors that this course belongs to.
     */
    public function majors()
    {
        return $this->belongsToMany(Major::class, 'major_courses', 'course_id', 'major_id');
    }

    /**
     * Get the doctors teaching this course.
     */
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_courses', 'course_id', 'doctor_id')
                    ->withPivot('doctor_course_id');
    }

    /**
     * Get the students enrolled in this course.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_courses', 'course_id', 'student_id')
                    ->withPivot('student_course');
    }

    /**
     * Get the exams for this course.
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'course_id');
    }
}
