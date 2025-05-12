<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exam;
use App\Models\Answer;
use App\Models\StudentExam;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GradingController extends Controller
{
    /**
     * Get all results for a specific exam
     *
     * @param string $id The exam ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getExamResults($id)
    {
        $exam = Exam::findOrFail($id);
        
        // Check if the authenticated doctor is authorized to access this exam
        if (Auth::user()->id != $exam->created_by) {
            return response()->json([
                'message' => 'Unauthorized to access this exam'
            ], 403);
        }
        
        $results = StudentExam::where('exam_id', $id)
            ->with('student:id,name')
            ->get()
            ->map(function ($studentExam) {
                return [
                    'student_id' => $studentExam->student_id,
                    'student_name' => $studentExam->student->name,
                    'score' => $studentExam->score,
                    'submitted_at' => $studentExam->submitted_at,
                    'graded' => !is_null($studentExam->graded_at)
                ];
            });
            
        return response()->json([
            'data' => $results
        ]);
    }
    
    /**
     * Grade a specific answer
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id The answer ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function gradeAnswer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'score' => 'required|numeric|min:0',
            'feedback' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $answer = Answer::with('question.exam')->findOrFail($id);
        
        // Check if the authenticated doctor is authorized to grade this answer
        if (Auth::user()->id != $answer->question->exam->created_by) {
            return response()->json([
                'message' => 'Unauthorized to grade this answer'
            ], 403);
        }
        
        // Check if the answer is for a written question (MCQ answers are auto-graded)
        if ($answer->question->type !== 'written') {
            return response()->json([
                'message' => 'Only written answers can be manually graded'
            ], 400);
        }
        
        $answer->score = $request->score;
        $answer->feedback = $request->feedback;
        $answer->graded_at = now();
        $answer->graded_by = Auth::user()->id;
        $answer->save();
        
        // Calculate the new total score for the student exam
        $this->recalculateStudentExamScore($answer->student_exam_id);
        
        return response()->json([
            'success' => true,
            'message' => 'Answer graded successfully'
        ]);
    }
    
    /**
     * Assign a final grade to a student's exam
     *
     * @param \Illuminate\Http\Request $request
     * @param string $examId The exam ID
     * @param string $studentId The student ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignFinalGrade(Request $request, $examId, $studentId)
    {
        $validator = Validator::make($request->all(), [
            'score' => 'required|numeric|min:0',
            'feedback' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $exam = Exam::findOrFail($examId);
        
        // Check if the authenticated doctor is authorized to grade this exam
        if (Auth::user()->id != $exam->created_by) {
            return response()->json([
                'message' => 'Unauthorized to grade this exam'
            ], 403);
        }
        
        $studentExam = StudentExam::where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->first();
            
        if (!$studentExam) {
            return response()->json([
                'message' => 'Student exam record not found'
            ], 404);
        }
        
        // Check if the exam has been submitted
        if (!$studentExam->submitted_at) {
            return response()->json([
                'message' => 'Cannot grade an exam that has not been submitted'
            ], 400);
        }
        
        $studentExam->score = $request->score;
        $studentExam->feedback = $request->feedback;
        $studentExam->graded_at = now();
        $studentExam->graded_by = Auth::user()->id;
        $studentExam->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Final grade assigned successfully'
        ]);
    }
    
    /**
     * Recalculate the total score for a student exam based on individual answer scores
     *
     * @param string $studentExamId The student exam ID
     * @return void
     */
    private function recalculateStudentExamScore($studentExamId)
    {
        $studentExam = StudentExam::findOrFail($studentExamId);
        
        // Get all answers for this student exam
        $answers = Answer::where('student_exam_id', $studentExamId)
            ->with('question:id,weight')
            ->get();
            
        $totalScore = 0;
        $totalWeight = 0;
        
        foreach ($answers as $answer) {
            // Only include graded answers in the calculation
            if ($answer->graded_at) {
                $weight = $answer->question->weight ?? 1;
                $totalScore += $answer->score * $weight;
                $totalWeight += $weight;
            }
        }
        
        // Calculate the weighted average score if there are graded answers
        if ($totalWeight > 0) {
            $finalScore = $totalScore / $totalWeight;
            
            // Update the student exam score
            $studentExam->score = $finalScore;
            $studentExam->save();
        }
    }
}