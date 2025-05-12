<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'course_id',
        'exam_date',
        'duration',
        'instructions',
        'status',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'exam_date' => 'datetime',
        'duration' => 'integer',
    ];

    /**
     * Get the course that this exam belongs to.
     */
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Get the user who created this exam.
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the questions in this exam.
     */
    public function questions()
    {
        return $this->belongsToMany(Question::class, 'exam_question')
            ->withPivot('id', 'weight');
    }

    /**
     * Get the grades for this exam.
     */
    public function grades()
    {
        return $this->hasMany(Grade::class, 'exam_id');
    }

    /**
     * Calculate the end time of the exam.
     */
    public function getEndTimeAttribute()
    {
        return $this->exam_date->add($this->exam_duration);
    }

    /**
     * Check if the exam is ongoing.
     */
    public function getIsOngoingAttribute()
    {
        $now = now();
        return $now->greaterThanOrEqualTo($this->exam_date) && 
               $now->lessThan($this->getEndTimeAttribute());
    }

    /**
     * Check if the exam is completed.
     */
    public function getIsCompletedAttribute()
    {
        return now()->greaterThan($this->getEndTimeAttribute());
    }
    public function studentExams()
    {
        return $this->hasMany(StudentExam::class);
    }
}

