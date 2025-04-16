<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Major;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    /**
     * Get system statistics.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get user counts by role
        $userCounts = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();
            
        // Get course counts
        $courseCount = Course::count();
        
        // Get exam counts by status
        $examCounts = Exam::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
            
        // Get major counts
        $majorCount = Major::count();
        
        // Get recent exams
        $recentExams = Exam::with(['course'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get course enrollment statistics
        $courseEnrollments = Course::select('courses.course_name', DB::raw('count(student_courses.student_id) as student_count'))
            ->leftJoin('student_courses', 'courses.course_id', '=', 'student_courses.course_id')
            ->groupBy('courses.course_id', 'courses.course_name')
            ->orderBy('student_count', 'desc')
            ->limit(10)
            ->get();
            
        // Get average grades by course
        $averageGrades = Course::select('courses.course_name', DB::raw('avg(grades.grade) as average_grade'))
            ->leftJoin('exams', 'courses.course_id', '=', 'exams.course_id')
            ->leftJoin('grades', 'exams.exam_id', '=', 'grades.exam_id')
            ->groupBy('courses.course_id', 'courses.course_name')
            ->having(DB::raw('avg(grades.grade)'), '>', 0)
            ->orderBy('average_grade', 'desc')
            ->get();
            
        return response()->json([
            'users' => [
                'total' => array_sum($userCounts),
                'by_role' => $userCounts
            ],
            'courses' => [
                'total' => $courseCount,
                'enrollments' => $courseEnrollments
            ],
            'exams' => [
                'total' => array_sum($examCounts),
                'by_status' => $examCounts,
                'recent' => $recentExams
            ],
            'majors' => [
                'total' => $majorCount
            ],
            'academic' => [
                'average_grades' => $averageGrades
            ]
        ]);
    }
}