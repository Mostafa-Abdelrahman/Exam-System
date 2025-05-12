<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use HasFactory;

    protected $fillable = [
        'major_name',
        'description',
    ];

     /**
     * Get the students in this major.
     */
    public function students()
    {
        return $this->hasMany(Student::class, 'major_id');
    }

    /**
     * Get the doctors in this major.
     */
    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'major_id');
    }

    /**
     * Get the courses that belong to this major.
     */
    public function courses()
    {
        return $this->belongsToMany(Course::class, 'major_courses', 'major_id', 'course_id');
    }
}
