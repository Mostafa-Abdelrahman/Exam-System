<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /**
     * Get all questions created by the doctor
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $doctorId = $request->query('doctor_id', Auth::id());
        
        $questions = Question::where('created_by', $doctorId)
            ->get()
            ->map(function ($question) {
                return [
                    'id' => $question->id,
                    'text' => $question->text,
                    'type' => $question->type,
                    'chapter' => $question->chapter,
                    'difficulty' => $question->difficulty,
                    'created_by' => $question->created_by,
                    'evaluation_criteria' => $question->evaluation_criteria
                ];
            });

        return response()->json([
            'data' => $questions
        ]);
    }

    /**
     * Create a new question
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'type' => 'required|in:mcq,written',
            'chapter' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'evaluation_criteria' => 'required_if:type,written|string|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $question = Question::create([
            'text' => $request->text,
            'type' => $request->type,
            'chapter' => $request->chapter,
            'difficulty' => $request->difficulty,
            'created_by' => Auth::id(),
            'evaluation_criteria' => $request->evaluation_criteria
        ]);

        return response()->json([
            'data' => [
                'id' => $question->id,
                'text' => $question->text,
                'type' => $question->type,
                'chapter' => $question->chapter,
                'difficulty' => $question->difficulty,
                'created_by' => $question->created_by,
                'evaluation_criteria' => $question->evaluation_criteria
            ]
        ], 201);
    }

    /**
     * Update a question
     * 
     * @param Request $request
     * @param string $id Question ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $question = Question::where('id', $id)
            ->where('created_by', Auth::id())
            ->first();

        if (!$question) {
            return response()->json([
                'message' => 'Question not found or you do not have permission to update it'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'type' => 'required|in:mcq,written',
            'chapter' => 'required|string',
            'difficulty' => 'required|in:easy,medium,hard',
            'evaluation_criteria' => 'required_if:type,written|string|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $question->update([
            'text' => $request->text,
            'type' => $request->type,
            'chapter' => $request->chapter,
            'difficulty' => $request->difficulty,
            'evaluation_criteria' => $request->evaluation_criteria
        ]);

        return response()->json([
            'data' => [
                'id' => $question->id,
                'text' => $question->text,
                'type' => $question->type,
                'chapter' => $question->chapter,
                'difficulty' => $question->difficulty,
                'created_by' => $question->created_by,
                'evaluation_criteria' => $question->evaluation_criteria
            ]
        ]);
    }

    /**
     * Delete a question
     * 
     * @param string $id Question ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $question = Question::where('id', $id)
            ->where('created_by', Auth::id())
            ->first();

        if (!$question) {
            return response()->json([
                'message' => 'Question not found or you do not have permission to delete it'
            ], 404);
        }

        // Check if question is used in any exams
        if ($question->examQuestions()->exists()) {
            return response()->json([
                'message' => 'This question cannot be deleted as it is being used in one or more exams'
            ], 400);
        }

        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully'
        ]);
    }
}