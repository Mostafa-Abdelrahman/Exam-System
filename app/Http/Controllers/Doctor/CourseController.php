<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    /**
     * Get all courses assigned to the doctor
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $doctorId = $request->query('doctor_id', Auth::id());
        
        $courses = Course::whereHas('doctors', function ($query) use ($doctorId) {
            $query->where('users.id', $doctorId);
        })
        ->withCount('students')
        ->get()
        ->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'code' => $course->code,
                'student_count' => $course->students_count
            ];
        });

        return response()->json([
            'data' => $courses
        ]);
    }

    /**
     * Get all students enrolled in a specific course
     * 
     * @param string $id Course ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudents($id)
    {
        // Check if the doctor is assigned to this course
        $isAssigned = Course::whereHas('doctors', function ($query) {
            $query->where('users.id', Auth::id());
        })->where('id', $id)->exists();

        if (!$isAssigned) {
            return response()->json([
                'message' => 'You are not authorized to view students for this course'
            ], 403);
        }

        $students = User::whereHas('courses', function ($query) use ($id) {
            $query->where('courses.id', $id);
        })
        ->where('role', 'student')
        ->get(['id', 'name', 'email']);

        return response()->json([
            'data' => $students
        ]);
    }
}