<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\StudentExamAnswer;
use App\Models\Grade;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class StudentResultController extends Controller
{
    /**
     * View all exam results
     * GET /api/student/results
     */
    public function getAllResults()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }
        
        $results = Grade::where('student_id', $student->id)
            ->whereNotNull('graded_at')
            ->with(['exam' => function($query) {
                $query->with('course');
            }])
            ->orderBy('graded_at', 'desc')
            ->get()
            ->map(function($grade) {
                return [
                    'exam_id' => $grade->exam_id,
                    'exam_name' => $grade->exam->exam_name,
                    'course_name' => $grade->exam->course->course_name,
                    'grade' => $grade->grade,
                    'letter_grade' => $grade->getLetterGradeAttribute(),
                    'passed' => $grade->getIsPassingAttribute(),
                    'graded_at' => $grade->graded_at
                ];
            });
            
        return response()->json([
            'results' => $results
        ]);
    }
    
    /**
     * View specific exam results
     * GET /api/student/results/:examId
     */
    public function getExamResult($examId)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }
        
        $grade = Grade::where('student_id', $student->id)
            ->where('exam_id', $examId)
            ->whereNotNull('graded_at')
            ->with(['exam' => function($query) {
                $query->with(['course', 'questions' => function($q) {
                    $q->with(['question' => function($qu) {
                        $qu->with(['choice']);
                    }]);
                }]);
            }])
            ->firstOrFail();
            
        // Get student's answers
        $answers = StudentExamAnswer::where('student_id', $student->id)
            ->whereIn('exam_question_id', $grade->exam->questions->pluck('id'))
            ->get()
            ->keyBy('exam_question_id');
            
        // Format questions with correct answers and student answers
        $questions = $grade->exam->questions->map(function($examQuestion) use ($answers) {
            $question = $examQuestion->question;
            $studentAnswer = $answers->get($examQuestion->id);
            
            $data = [
                'question_id' => $question->id,
                'question_text' => $question->question_text,
                'weight' => $examQuestion->weight,
                'type' => $question->questionType->question_type
            ];
            
            if ($question->isMultipleChoice()) {
                $correctChoice = $question->correctChoice();
                $data['choices'] = $question->choice->map(function($choice) {
                    return [
                        'id' => $choice->id,
                        'text' => $choice->choice_text,
                        'is_correct' => $choice->is_correct
                    ];
                });
                
                if ($studentAnswer) {
                    $data['student_answer_id'] = $studentAnswer->written_answer;
                    $data['is_correct'] = ($studentAnswer->written_answer == $correctChoice->id);
                }
            } elseif ($question->isWrittenQuestion()) {
                $data['evaluation_criteria'] = $question->writtenQuestion->evaluation_criteria;
                
                if ($studentAnswer) {
                    $data['student_answer'] = $studentAnswer->written_answer;
                    $data['graded'] = $studentAnswer->graded;
                }
            }
            
            return $data;
        });
        
        return response()->json([
            'exam' => [
                'id' => $grade->exam->id,
                'name' => $grade->exam->exam_name,
                'course' => $grade->exam->course->course_name,
                'date' => $grade->exam->exam_date,
                'duration' => $grade->exam->exam_duration
            ],
            'grade' => [
                'score' => $grade->grade,
                'letter_grade' => $grade->getLetterGradeAttribute(),
                'passed' => $grade->getIsPassingAttribute(),
                'graded_at' => $grade->graded_at
            ],
            'questions' => $questions
        ]);
    }
}