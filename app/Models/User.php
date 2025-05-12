<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role', 
        'gender',// Added role attribute
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => 'string', // Cast role as a string
        ];
    }

    /**
     * Get the user's initials.
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    /**
     * Check if the user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->hashed_password;
    }

    /**
     * Get the student record associated with the user.
     */
    public function student()
    {
        return $this->hasOne(Student::class, 'user_id');
    }

    /**
     * Get the doctor record associated with the user.
     */
    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if the user is a doctor.
     */
    public function isDoctor()
    {
        return $this->role === 'doctor';
    }

    /**
     * Check if the user is a student.
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * Get the exams created by this user (if doctor).
     */
    public function createdExams()
    {
        return $this->hasMany(Exam::class, 'created_by');
    }
    public function courses()
    {
        if ($this->role === 'student') {
            return $this->belongsToMany(Course::class, 'student_course');
        } elseif ($this->role === 'doctor') {
            return $this->belongsToMany(Course::class, 'doctor_course', 'doctor_id');
        }
        
        return null;
    }

    /**
     * Get the questions created by the doctor.
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    /**
     * Get the exams created by the doctor.
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'created_by');
    }

    /**
     * Get the student exams.
     */
    public function studentExams()
    {
        return $this->hasMany(StudentExam::class, 'student_id');
    }
    // ----------------------------------------------------------------------
    public function enrolledCourses()
{
    return $this->belongsToMany(Course::class, 'course_user', 'user_id', 'course_id')
                ->withTimestamps();
}
}

