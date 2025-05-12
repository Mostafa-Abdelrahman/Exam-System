<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AssignmentController extends Controller
{
    /**
     * Assign a doctor to a course.
     *
     * @param  string  $doctorId
     * @param  string  $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignDoctorToCourse($doctorId, $courseId)
    {
        // Validate that the doctor exists and has the doctor role
        $doctor = User::where('id', $doctorId)
            ->where('role', 'doctor')
            ->firstOrFail();
            
        // Validate that the course exists
        $course = Course::findOrFail($courseId);
        
        // Check if the doctor is already assigned to the course
        if ($course->doctors()->where('user_id', $doctorId)->exists()) {
            return response()->json([
                'message' => 'Doctor is already assigned to this course'
            ], 422);
        }
        
        // Assign the doctor to the course
        $course->doctors()->attach($doctorId);
        
        return response()->json([
            'success' => true,
            'message' => 'Doctor assigned to course successfully'
        ]);
    }
    
    /**
     * Remove a doctor from a course.
     *
     * @param  string  $doctorId
     * @param  string  $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeDoctorFromCourse($doctorId, $courseId)
    {
        // Validate that the doctor exists and has the doctor role
        $doctor = User::where('id', $doctorId)
            ->where('role', 'doctor')
            ->firstOrFail();
            
        // Validate that the course exists
        $course = Course::findOrFail($courseId);
        
        // Remove the doctor from the course
        $course->doctors()->detach($doctorId);
        
        return response()->json([
            'success' => true,
            'message' => 'Doctor removed from course successfully'
        ]);
    }
    
    /**
     * Enroll a student in a course.
     *
     * @param  string  $studentId
     * @param  string  $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function enrollStudentInCourse($studentId, $courseId)
    {
        // Validate that the student exists and has the student role
        $student = User::where('id', $studentId)
            ->where('role', 'student')
            ->firstOrFail();
            
        // Validate that the course exists
        $course = Course::findOrFail($courseId);
        
        // Check if the student is already enrolled in the course
        if ($course->students()->where('user_id', $studentId)->exists()) {
            return response()->json([
                'message' => 'Student is already enrolled in this course'
            ], 422);
        }
        
        // Enroll the student in the course
        $course->students()->attach($studentId);
        
        return response()->json([
            'success' => true,
            'message' => 'Student enrolled in course successfully'
        ]);
    }
    
    /**
     * Remove a student from a course.
     *
     * @param  string  $studentId
     * @param  string  $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeStudentFromCourse($studentId, $courseId)
    {
        // Validate that the student exists and has the student role
        $student = User::where('id', $studentId)
            ->where('role', 'student')
            ->firstOrFail();
            
        // Validate that the course exists
        $course = Course::findOrFail($courseId);
        
        // Remove the student from the course
        $course->students()->detach($studentId);
        
        return response()->json([
            'success' => true,
            'message' => 'Student removed from course successfully'
        ]);
    }
}