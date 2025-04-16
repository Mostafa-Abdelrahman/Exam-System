<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Major;
use App\Models\MajorCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * Display a listing of all courses.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $courses = Course::query();
        
        // Filter by major if provided
        if ($request->has('major_id')) {
            $courses->whereHas('majors', function($query) use ($request) {
                $query->where('major_id', $request->major_id);
            });
        }
        
        // Include relationships if requested
        if ($request->has('with_majors')) {
            $courses->with('majors');
        }
        
        if ($request->has('with_doctors')) {
            $courses->with('doctors');
        }
        
        if ($request->has('with_students')) {
            $courses->with('students');
        }
        
        // Pagination
        $courses = $courses->paginate($request->per_page ?? 15);
        
        return response()->json([
            'courses' => $courses,
            'message' => 'Courses retrieved successfully'
        ]);
    }

    /**
     * Store a newly created course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_name' => 'required|string|max:255',
            'course_code' => 'required|string|max:20|unique:courses,course_code',
            'description' => 'nullable|string',
            'major_ids' => 'sometimes|array',
            'major_ids.*' => 'exists:majors,major_id',
        ]);
        
        DB::beginTransaction();
        
        try {
            $course = Course::create([
                'course_name' => $validated['course_name'],
                'course_code' => $validated['course_code'],
                'description' => $validated['description'] ?? null,
            ]);
            
            // Associate course with majors if provided
            if (isset($validated['major_ids']) && is_array($validated['major_ids'])) {
                foreach ($validated['major_ids'] as $majorId) {
                    MajorCourse::create([
                        'major_id' => $majorId,
                        'course_id' => $course->course_id,
                    ]);
                }
            }
            
            DB::commit();
            
            // Load the majors relationship
            $course->load('majors');
            
            return response()->json([
                'course' => $course,
                'message' => 'Course created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating course: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified course.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $course = Course::with(['majors', 'doctors', 'students'])->findOrFail($id);
        
        return response()->json([
            'course' => $course,
            'message' => 'Course details retrieved successfully'
        ]);
    }

    /**
     * Update the specified course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        
        $validated = $request->validate([
            'course_name' => 'sometimes|string|max:255',
            'course_code' => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('courses')->ignore($id, 'course_id'),
            ],
            'description' => 'nullable|string',
            'major_ids' => 'sometimes|array',
            'major_ids.*' => 'exists:majors,major_id',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update course details
            $course->update([
                'course_name' => $validated['course_name'] ?? $course->course_name,
                'course_code' => $validated['course_code'] ?? $course->course_code,
                'description' => $validated['description'] ?? $course->description,
            ]);
            
            // Update course majors if provided
            if (isset($validated['major_ids'])) {
                // Remove existing associations
                MajorCourse::where('course_id', $course->course_id)->delete();
                
                // Create new associations
                foreach ($validated['major_ids'] as $majorId) {
                    MajorCourse::create([
                        'major_id' => $majorId,
                        'course_id' => $course->course_id,
                    ]);
                }
            }
            
            DB::commit();
            
            // Reload the course with relationships
            $course = Course::with(['majors'])->findOrFail($id);
            
            return response()->json([
                'course' => $course,
                'message' => 'Course updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating course: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified course.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        
        // Delete the course (cascade will handle related records)
        $course->delete();
        
        return response()->json([
            'message' => 'Course deleted successfully'
        ]);
    }
}