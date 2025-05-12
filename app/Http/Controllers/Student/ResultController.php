<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\StudentExam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    /**
     * Get all exam results for the student
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $student = Auth::user();
        
        $results = StudentExam::where('user_id', $student->id)
            ->whereNotNull('submitted_at')
            ->with(['exam.course' => function($query) {
                $query->select('id', 'name');
            }])
            ->get()
            ->map(function ($studentExam) {
                $status = $studentExam->areAllQuestionsGraded() ? 'graded' : 'completed';
                
                return [
                    'exam_id' => $studentExam->exam_id,
                    'exam_name' => $studentExam->exam->name,
                    'course_name' => $studentExam->exam->course->name,
                    'score' => $studentExam->score,
                    'status' => $status,
                    'submitted_at' => $studentExam->submitted_at
                ];
            });
        
        return response()->json([
            'data' => $results
        ]);
    }

    /**
     * Get results for a specific exam
     *
     * @param string $examId
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($examId)
    {
        $student = Auth::user();
        
        $studentExam = StudentExam::where('user_id', $student->id)
            ->where('exam_id', $examId)
            ->whereNotNull('submitted_at')
            ->with([
                'exam.course:id,name',
                'answers.question:id,text',
            ])
            ->firstOrFail();
        
        // Get each answer with corresponding question and correct answer
        $answers = $studentExam->answers->map(function ($answer) {
            $question = $answer->question;
            $correctAnswer = null;
            
            if ($question->type === 'mcq') {
                $correctChoice = $question->choices()->where('is_correct', true)->first();
                if ($correctChoice) {
                    $correctAnswer = $correctChoice->text;
                }
                
                // Get the text of the selected answer
                $selectedChoice = $question->choices()->where('id', $answer->answer)->first();
                $answerText = $selectedChoice ? $selectedChoice->text : 'No answer';
            } else {
                $answerText = $answer->answer;
            }
            
            return [
                'question_text' => $question->text,
                'answer' => $answerText,
                'correct_answer' => $correctAnswer,
                'score' => $answer->score,
                'feedback' => $answer->feedback
            ];
        });
        
        $result = [
            'exam_id' => $studentExam->exam_id,
            'exam_name' => $studentExam->exam->name,
            'course_name' => $studentExam->exam->course->name,
            'score' => $studentExam->score,
            'submitted_at' => $studentExam->submitted_at,
            'answers' => $answers
        ];
        
        return response()->json([
            'data' => $result
        ]);
    }
}