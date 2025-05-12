<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'major_id',
        'specialization',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class, 'major_id');
    }
    /**
     * Get the courses that this doctor teaches.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'doctor_courses', 'doctor_id', 'course_id')
                    ->withPivot('doctor_course_id');
    }

    /**
     * Get the exams created by this doctor.
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'created_by', 'user_id');
    }
}
