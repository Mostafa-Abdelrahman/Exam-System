<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Course;
use App\Models\ExamQuestion;
use App\Models\QuestionBank;
use App\Models\Grade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /** 
     * Display a listing of all exams.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $exams = Exam::query();
        
        // Filter by course if provided
        if ($request->has('course_id')) {
            $exams->where('course_id', $request->course_id);
        }
        
        // Filter by status if provided
        if ($request->has('status')) {
            $exams->where('status', $request->status);
        }
        
        // Filter by creator if provided
        if ($request->has('created_by')) {
            $exams->where('created_by', $request->created_by);
        }
        
        // Filter by date range if provided
        if ($request->has('from_date') && $request->has('to_date')) {
            $exams->whereBetween('exam_date', [$request->from_date, $request->to_date]);
        }
        
        // Include relationships
        $exams->with(['course', 'creator' => function($query) {
            $query->select('user_id', 'name', 'email');
        }]);
        
        // Count questions
        $exams->withCount('questions');
        
        // Pagination
        $exams = $exams->paginate($request->per_page ?? 15);
        
        return response()->json([
            'exams' => $exams,
            'message' => 'Exams retrieved successfully'
        ]);
    }
    
    /**
     * Display the specified exam.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $exam = Exam::with([
            'course', 
            'creator' => function($query) {
                $query->select('user_id', 'name', 'email');
            },
            'questions' => function($query) {
                $query->with(['question' => function($q) {
                    $q->with(['choices', 'writtenQuestion']);
                }]);
            }
        ])->findOrFail($id);
        
        // Get grade statistics
        $gradeStats = Grade::where('exam_id', $id)
            ->select(
                DB::raw('count(*) as total_grades'),
                DB::raw('avg(grade) as average_grade'),
                DB::raw('min(grade) as lowest_grade'),
                DB::raw('max(grade) as highest_grade')
            )
            ->first();
        
        $exam->grade_statistics = $gradeStats;
        
        return response()->json([
            'exam' => $exam,
            'message' => 'Exam details retrieved successfully'
        ]);
    }
    
    /**
     * Update the status of an exam.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:draft,published,archived',
        ]);
        
        $exam->update([
            'status' => $validated['status']
        ]);
        
        return response()->json([
            'exam' => $exam,
            'message' => 'Exam status updated successfully'
        ]);
    }
    
    /**
     * Delete an exam.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $exam = Exam::findOrFail($id);
        
        // Delete the exam (cascade will handle related records)
        $exam->delete();
        
        return response()->json([
            'message' => 'Exam deleted successfully'
        ]);
    }
    
    /**
     * Get exam results.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getResults($id)
    {
        $exam = Exam::findOrFail($id);
        
        $results = Grade::where('exam_id', $id)
            ->with([
                'student' => function($query) {
                    $query->with(['user:user_id,name,email']);
                }
            ])
            ->orderBy('grade', 'desc')
            ->get();
        
        // Calculate statistics
        $stats = [
            'total_students' => $results->count(),
            'average_grade' => $results->avg('grade'),
            'highest_grade' => $results->max('grade'),
            'lowest_grade' => $results->min('grade'),
            'passing_count' => $results->where('grade', '>=', 60)->count(),
            'failing_count' => $results->where('grade', '<', 60)->count(),
        ];
        
        return response()->json([
            'exam' => [
                'id' => $exam->exam_id,
                'name' => $exam->exam_name,
                'date' => $exam->exam_date,
            ],
            'results' => $results,
            'statistics' => $stats,
            'message' => 'Exam results retrieved successfully'
        ]);
    }
}