<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MajorCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'major_id',
        'course_id',
    ];

    // Relationships
    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
