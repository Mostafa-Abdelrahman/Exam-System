<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Choice;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChoiceController extends Controller
{
    /**
     * Get all choices for a question
     * 
     * @param string $questionId Question ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($questionId)
    {
        // Check if question belongs to the authenticated doctor
        $question = Question::where('id', $questionId)
            ->where('created_by', Auth::id())
            ->first();

        if (!$question) {
            return response()->json([
                'message' => 'Question not found or you do not have permission to view its choices'
            ], 404);
        }

        $choices = Choice::where('question_id', $questionId)
            ->get()
            ->map(function ($choice) {
                return [
                    'id' => $choice->id,
                    'question_id' => $choice->question_id,
                    'text' => $choice->text,
                    'is_correct' => (bool) $choice->is_correct
                ];
            });

        return response()->json([
            'data' => $choices
        ]);
    }

    /**
     * Create a new choice for a question
     * 
     * @param Request $request
     * @param string $questionId Question ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $questionId)
    {
        // Check if question belongs to the authenticated doctor
        $question = Question::where('id', $questionId)
            ->where('created_by', Auth::id())
            ->first();

        if (!$question) {
            return response()->json([
                'message' => 'Question not found or you do not have permission to add choices to it'
            ], 404);
        }

        // Check if the question is MCQ type
        if ($question->type !== 'mcq') {
            return response()->json([
                'message' => 'Choices can only be added to MCQ questions'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'is_correct' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // If this is marked as correct, update other choices to be incorrect
        if ($request->is_correct) {
            Choice::where('question_id', $questionId)
                ->update(['is_correct' => false]);
        }

        $choice = Choice::create([
            'question_id' => $questionId,
            'text' => $request->text,
            'is_correct' => $request->is_correct
        ]);

        return response()->json([
            'data' => [
                'id' => $choice->id,
                'question_id' => $choice->question_id,
                'text' => $choice->text,
                'is_correct' => (bool) $choice->is_correct
            ]
        ], 201);
    }

    /**
     * Update a choice
     * 
     * @param Request $request
     * @param string $id Choice ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        // Find the choice
        $choice = Choice::find($id);

        if (!$choice) {
            return response()->json([
                'message' => 'Choice not found'
            ], 404);
        }

        // Check if the associated question belongs to the authenticated doctor
        $question = Question::where('id', $choice->question_id)
            ->where('created_by', Auth::id())
            ->first();

        if (!$question) {
            return response()->json([
                'message' => 'You do not have permission to update this choice'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'text' => 'required|string',
            'is_correct' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // If this is being marked as correct, update other choices to be incorrect
        if ($request->is_correct && !$choice->is_correct) {
            Choice::where('question_id', $choice->question_id)
                ->update(['is_correct' => false]);
        }

        $choice->update([
            'text' => $request->text,
            'is_correct' => $request->is_correct
        ]);

        return response()->json([
            'data' => [
                'id' => $choice->id,
                'question_id' => $choice->question_id,
                'text' => $choice->text,
                'is_correct' => (bool) $choice->is_correct
            ]
        ]);
    }

    /**
     * Delete a choice
     * 
     * @param string $id Choice ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        // Find the choice
        $choice = Choice::find($id);

        if (!$choice) {
            return response()->json([
                'message' => 'Choice not found'
            ], 404);
        }

        // Check if the associated question belongs to the authenticated doctor
        $question = Question::where('id', $choice->question_id)
            ->where('created_by', Auth::id())
            ->first();

        if (!$question) {
            return response()->json([
                'message' => 'You do not have permission to delete this choice'
            ], 403);
        }

        // Check if this is the only choice or the only correct answer
        $choiceCount = Choice::where('question_id', $choice->question_id)->count();
        
        if ($choiceCount <= 1) {
            return response()->json([
                'message' => 'Cannot delete the only choice for this question'
            ], 400);
        }
        
        if ($choice->is_correct) {
            $correctChoices = Choice::where('question_id', $choice->question_id)
                ->where('is_correct', true)
                ->count();
                
            if ($correctChoices <= 1) {
                return response()->json([
                    'message' => 'Cannot delete the only correct choice for this question'
                ], 400);
            }
        }

        $choice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Choice deleted successfully'
        ]);
    }
}