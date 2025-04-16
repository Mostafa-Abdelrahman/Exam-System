<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorCourse;
use App\Models\StudentCourse;
use App\Models\User;
use App\Models\Course;
use App\Models\Doctor;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    /**
     * Assign a doctor to a course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $doctorId
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function assignDoctorToCourse($doctorId, $courseId)
    {
        // Verify the doctor exists and has role 'doctor'
        $doctor = Doctor::where('user_id', $doctorId)->firstOrFail();
        
        // Verify the course exists
        $course = Course::findOrFail($courseId);
        
        // Check if the assignment already exists
        $existingAssignment = DoctorCourse::where('doctor_id', $doctor->user_id)
            ->where('course_id', $courseId)
            ->first();
            
        if ($existingAssignment) {
            return response()->json([
                'message' => 'Doctor is already assigned to this course'
            ], 409);
        }
        
        // Create the assignment
        $assignment = DoctorCourse::create([
            'doctor_id' => $doctor->user_id,
            'course_id' => $courseId
        ]);
        
        return response()->json([
            'assignment' => $assignment,
            'message' => 'Doctor assigned to course successfully'
        ], 201);
    }
    
    /**
     * Enroll a student in a course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $studentId
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function enrollStudentInCourse($studentId, $courseId)
    {
        // Verify the student exists and has role 'student'
        $student = Student::where('user_id', $studentId)->firstOrFail();
        
        // Verify the course exists
        $course = Course::findOrFail($courseId);
        
        // Check if the enrollment already exists
        $existingEnrollment = StudentCourse::where('student_id', $student->user_id)
            ->where('course_id', $courseId)
            ->first();
            
        if ($existingEnrollment) {
            return response()->json([
                'message' => 'Student is already enrolled in this course'
            ], 409);
        }
        
        // Create the enrollment
        $enrollment = StudentCourse::create([
            'student_id' => $student->user_id,
            'course_id' => $courseId
        ]);
        
        return response()->json([
            'enrollment' => $enrollment,
            'message' => 'Student enrolled in course successfully'
        ], 201);
    }
    
    /**
     * Remove a doctor from a course.
     *
     * @param  int  $doctorId
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function removeDoctorFromCourse($doctorId, $courseId)
    {
        $assignment = DoctorCourse::where('doctor_id', $doctorId)
            ->where('course_id', $courseId)
            ->firstOrFail();
            
        $assignment->delete();
        
        return response()->json([
            'message' => 'Doctor removed from course successfully'
        ]);
    }
    
    /**
     * Remove a student from a course.
     *
     * @param  int  $studentId
     * @param  int  $courseId
     * @return \Illuminate\Http\Response
     */
    public function removeStudentFromCourse($studentId, $courseId)
    {
        $enrollment = StudentCourse::where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->firstOrFail();
            
        $enrollment->delete();
        
        return response()->json([
            'message' => 'Student removed from course successfully'
        ]);
    }
}