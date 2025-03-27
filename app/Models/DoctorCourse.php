<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'course_id',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
