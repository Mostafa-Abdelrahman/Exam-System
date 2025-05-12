<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Question;
use App\Models\StudentExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ExamController extends Controller
{
    /**
     * Get all exams created by the doctor
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $doctorId = $request->query('doctor_id', Auth::id());
        
        $exams = Exam::where('created_by', $doctorId)
            ->with('course:id,name,code')
            ->get()
            ->map(function ($exam) {
                // Check if there are student exams that need grading
                $needsGrading = StudentExam::where('exam_id', $exam->id)
                    ->where('status', 'completed')
                    ->where('graded', false)
                    ->exists();
                
                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'course_id' => $exam->course_id,
                    'exam_date' => $exam->exam_date,
                    'duration' => $exam->duration,
                    'instructions' => $exam->instructions,
                    'status' => $exam->status,
                    'created_by' => $exam->created_by,
                    'course' => [
                        'name' => $exam->course->name,
                        'code' => $exam->course->code
                    ],
                    'needs_grading' => $needsGrading
                ];
            });

        return response()->json([
            'data' => $exams
        ]);
    }

    /**
     * Create a new exam
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'course_id' => 'required|uuid|exists:courses,id',
            'exam_date' => 'required|date|after:now',
            'duration' => 'required|integer|min:1',
            'instructions' => 'required|string',
            'status' => 'required|in:draft,published,archived',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if doctor is assigned to this course
        $isAssigned = Course::whereHas('doctors', function ($query) {
            $query->where('users.id', Auth::id());
        })->where('id', $request->course_id)->exists();

        if (!$isAssigned) {
            return response()->json([
                'message' => 'You are not authorized to create an exam for this course'
            ], 403);
        }

        $exam = Exam::create([
            'name' => $request->name,
            'course_id' => $request->course_id,
            'exam_date' => $request->exam_date,
            'duration' => $request->duration,
            'instructions' => $request->instructions,
            'status' => $request->status,
            'created_by' => Auth::id()
        ]);

        return response()->json([
            'data' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'course_id' => $exam->course_id,
                'exam_date' => $exam->exam_date,
                'duration' => $exam->duration,
                'instructions' => $exam->instructions,
                'status' => $exam->status,
                'created_by' => $exam->created_by
            ]
        ], 201);
    }

    /**
     * Update an exam
     * 
     * @param Request $request
     * @param string $id Exam ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $exam = Exam::where('id', $id)
            ->where('created_by', Auth::id())
            ->first();

        if (!$exam) {
            return response()->json([
                'message' => 'Exam not found or you do not have permission to update it'
            ], 404);
        }

        // If exam is already published and has student exams, restrict certain updates
        $hasActiveStudentExams = StudentExam::where('exam_id', $id)->exists();
        
        $rules = [
            'name' => 'required|string|max:255',
            'course_id' => 'required|uuid|exists:courses,id',
            'instructions' => 'required|string',
            'status' => 'required|in:draft,published,archived',
        ];
        
        // Only allow changing date and duration if no students have taken the exam
        if (!$hasActiveStudentExams) {
            $rules['exam_date'] = 'required|date|after:now';
            $rules['duration'] = 'required|integer|min:1';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if doctor is assigned to the requested course
        $isAssigned = Course::whereHas('doctors', function ($query) {
            $query->where('users.id', Auth::id());
        })->where('id', $request->course_id)->exists();

        if (!$isAssigned) {
            return response()->json([
                'message' => 'You are not authorized to assign this exam to this course'
            ], 403);
        }

        $updateData = [
            'name' => $request->name,
            'course_id' => $request->course_id,
            'instructions' => $request->instructions,
            'status' => $request->status,
        ];
        
        // Only update date and duration if no students have taken the exam
        if (!$hasActiveStudentExams) {
            $updateData['exam_date'] = $request->exam_date;
            $updateData['duration'] = $request->duration;
        }

        $exam->update($updateData);

        return response()->json([
            'data' => [
                'id' => $exam->id,
                'name' => $exam->name,
                'course_id' => $exam->course_id,
                'exam_date' => $exam->exam_date,
                'duration' => $exam->duration,
                'instructions' => $exam->instructions,
                'status' => $exam->status,
                'created_by' => $exam->created_by
            ]
        ]);
    }

    /**
     * Delete an exam
     * 
     * @param string $id Exam ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $exam = Exam::where('id', $id)
            ->where('created_by', Auth::id())
            ->first();

        if (!$exam) {
            return response()->json([
                'message' => 'Exam not found or you do not have permission to delete it'
            ], 404);
        }

        // Check if exam has been attempted by any student
        $hasStudentExams = StudentExam::where('exam_id', $id)->exists();
        
        if ($hasStudentExams) {
            return response()->json([
                'message' => 'Cannot delete an exam that has been attempted by students'
            ], 400);
        }

        // Delete related exam questions first
        ExamQuestion::where('exam_id', $id)->delete();
        
        // Delete the exam
        $exam->delete();

        return response()->json([
            'success' => true,
            'message' => 'Exam deleted successfully'
        ]);
    }

    /**
     * Get all questions for an exam
     * 
     * @param string $id Exam ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getQuestions($id)
    {
        $exam = Exam::where('id', $id)
            ->where('created_by', Auth::id())
            ->first();

        if (!$exam) {
            return response()->json([
                'message' => 'Exam not found or you do not have permission to view its questions'
            ], 404);
        }

        $examQuestions = ExamQuestion::where('exam_id', $id)
            ->with('question:id,text,type')
            ->get()
            ->map(function ($examQuestion) {
                return [
                    'id' => $examQuestion->id,
                    'exam_id' => $examQuestion->exam_id,
                    'question_id' => $examQuestion->question_id,
                    'weight' => $examQuestion->weight,
                    'question' => [
                        'id' => $examQuestion->question->id,
                        'text' => $examQuestion->question->text,
                        'type' => $examQuestion->question->type
                    ]
                ];
            });

        return response()->json([
            'data' => $examQuestions
        ]);
    }

    /**
     * Add a question to an exam
     * 
     * @param Request $request
     * @param string $examId Exam ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function addQuestion(Request $request, $examId)
    {
        $exam = Exam::where('id', $examId)
            ->where('created_by', Auth::id())
            ->first();

        if (!$exam) {
            return response()->json([
                'message' => 'Exam not found or you do not have permission to modify it'
            ], 404);
        }

        // If exam has already been attempted, don't allow adding questions
        $hasStudentExams = StudentExam::where('exam_id', $examId)->exists();
        
        if ($hasStudentExams) {
            return response()->json([
                'message' => 'Cannot modify questions for an exam that has been attempted by students'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'question_id' => 'required|uuid|exists:questions,id',
            'weight' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if question belongs to this doctor
        $questionBelongsToDr = Question::where('id', $request->question_id)
            ->where('created_by', Auth::id())
            ->exists();
            
        if (!$questionBelongsToDr) {
            return response()->json([
                'message' => 'You can only add questions that you have created'
            ], 403);
        }

        // Check if question is already added to this exam
        $questionExists = ExamQuestion::where('exam_id', $examId)
            ->where('question_id', $request->question_id)
            ->exists();
            
        if ($questionExists) {
            return response()->json([
                'message' => 'This question is already added to this exam'
            ], 400);
        }

        $examQuestion = ExamQuestion::create([
            'exam_id' => $examId,
            'question_id' => $request->question_id,
            'weight' => $request->weight
        ]);

        return response()->json([
            'data' => [
                'id' => $examQuestion->id,
                'exam_id' => $examQuestion->exam_id,
                'question_id' => $examQuestion->question_id,
                'weight' => $examQuestion->weight
            ]
        ], 201);
    }

    /**
     * Remove a question from an exam
     * 
     * @param string $id ExamQuestion ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeQuestion($id)
    {
        $examQuestion = ExamQuestion::find($id);

        if (!$examQuestion) {
            return response()->json([
                'message' => 'Exam question not found'
            ], 404);
        }

        // Check if exam belongs to this doctor
        $examBelongsToDr = Exam::where('id', $examQuestion->exam_id)
            ->where('created_by', Auth::id())
            ->exists();
            
        if (!$examBelongsToDr) {
            return response()->json([
                'message' => 'You do not have permission to modify this exam'
            ], 403);
        }

        // If exam has already been attempted, don't allow removing questions
        $hasStudentExams = StudentExam::where('exam_id', $examQuestion->exam_id)->exists();
        
        if ($hasStudentExams) {
            return response()->json([
                'message' => 'Cannot modify questions for an exam that has been attempted by students'
            ], 400);
        }

        $examQuestion->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question removed from exam successfully'
        ]);
    }
}