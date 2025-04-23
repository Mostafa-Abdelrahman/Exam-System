<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\StudentExamAnswer;
use App\Models\Grade;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



class StudentCourseController extends Controller
{
    /**
     * Get all courses the authenticated student is enrolled in
     * GET /api/student/courses
     */
    public function getEnrolledCourses()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }
        
        $courses = $student->courses()->with('exams')->get();
        
        return response()->json([
            'courses' => $courses
        ]);
    }
}