<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Display a listing of courses.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $courses = Course::withCount(['students', 'doctors'])
            ->paginate($request->input('limit', 20));
        
        // Transform the data to match API spec
        $transformedCourses = $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'code' => $course->code,
                'student_count' => $course->students_count,
                'doctor_count' => $course->doctors_count
            ];
        });
        
        return response()->json([
            'data' => $transformedCourses,
            'meta' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total()
            ]
        ]);
    }

    /**
     * Store a newly created course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:courses',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Course::create([
            'name' => $request->name,
            'code' => $request->code,
            'description' => $request->description ?? '',
        ]);

        return response()->json([
            'data' => [
                'id' => $course->id,
                'name' => $course->name,
                'code' => $course->code,
                'description' => $course->description,
            ]
        ], 201);
    }

    /**
     * Update the specified course.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => [
                'sometimes',
                'required', 
                'string', 
                'max:20',
                Rule::unique('courses')->ignore($course->id),
            ],
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course->update($request->only(['name', 'code', 'description']));

        return response()->json([
            'data' => [
                'id' => $course->id,
                'name' => $course->name,
                'code' => $course->code,
                'description' => $course->description,
            ]
        ]);
    }

    /**
     * Remove the specified course.
     *
     * @param  string  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully'
        ]);
    }
}