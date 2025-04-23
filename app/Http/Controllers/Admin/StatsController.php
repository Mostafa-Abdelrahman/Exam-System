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
            
        // Get course counts with details
        $courses = Course::select('id', 'course_name', 'course_code')
            ->withCount(['students', 'doctors', 'exams'])
            ->get();
            
        // Get exam counts by status
        $examCounts = Exam::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();
            
        // Get major counts with details
        $majors = Major::select('id', 'major_name')
            ->withCount(['students', 'doctors', 'courses'])
            ->get();
            
        // Get recent exams with course details
        $recentExams = Exam::with(['course:id,course_name,course_code'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get course enrollment statistics
        $courseEnrollments = Course::select('id', 'course_name', 'course_code')
            ->withCount('students')
            ->orderBy('students_count', 'desc')
            ->limit(10)
            ->get();
            
        // Get average grades by course
        $averageGrades = Course::select('id', 'course_name', 'course_code')
            ->with(['exams.grades' => function($query) {
                $query->select('exam_id', DB::raw('AVG(grade) as average_grade'))
                    ->groupBy('exam_id');
            }])
            ->get()
            ->map(function($course) {
                $average = $course->exams->flatMap->grades->avg('average_grade');
                return [
                    'course_name' => $course->course_name,
                    'course_code' => $course->course_code,
                    'average_grade' => $average ? round($average, 2) : 0
                ];
            })
            ->sortByDesc('average_grade')
            ->values();
            
        return response()->json([
            'users' => [
                'total' => array_sum($userCounts),
                'by_role' => $userCounts
            ],
            'courses' => [
                'total' => $courses->count(),
                'details' => $courses,
                'enrollments' => $courseEnrollments
            ],
            'exams' => [
                'total' => array_sum($examCounts),
                'by_status' => $examCounts,
                'recent' => $recentExams
            ],
            'majors' => [
                'total' => $majors->count(),
                'details' => $majors
            ],
            'academic' => [
                'average_grades' => $averageGrades
            ]
        ]);
    }
}