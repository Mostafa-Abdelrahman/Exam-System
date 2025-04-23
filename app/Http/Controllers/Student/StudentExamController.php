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


class StudentExamController extends Controller
{
    /**
     * Get upcoming exams for the authenticated student
     * GET /api/student/exams/upcoming
     */
    public function getUpcomingExams()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }
        
        $enrolledCourseIds = $student->courses()->pluck('courses.id');
        
        $upcomingExams = Exam::whereIn('course_id', $enrolledCourseIds)
            ->where('exam_date', '>', now())
            ->orderBy('exam_date')
            ->with('course')
            ->get();
        
        return response()->json([
            'upcoming_exams' => $upcomingExams
        ]);
    }
    
    /**
     * Get currently available exams for the authenticated student
     * GET /api/student/exams/available
     */
    public function getAvailableExams()
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }
        
        $enrolledCourseIds = $student->courses()->pluck('courses.id');
        
        $now = now();
        $availableExams = Exam::whereIn('course_id', $enrolledCourseIds)
            ->where('exam_date', '<=', $now)
            ->where(DB::raw('DATE_ADD(exam_date, INTERVAL exam_duration MINUTE)'), '>=', $now)
            ->where('status', 'active')
            ->with('course')
            ->get();
        
        // Filter out exams the student has already started or completed
        $availableExams = $availableExams->filter(function($exam) use ($student) {
            return !Grade::where('student_id', $student->id)
                ->where('exam_id', $exam->id)
                ->exists();
        });
        
        return response()->json([
            'available_exams' => $availableExams
        ]);
    }
    
    /**
     * Get exam details
     * GET /api/student/exams/:id
     */
    public function getExamDetails($id)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }
        
        $exam = Exam::with(['course', 'questions'])
            ->findOrFail($id);
        
        // Check if student is enrolled in the course
        $isEnrolled = $student->courses()
            ->where('courses.id', $exam->course_id)
            ->exists();
            
        if (!$isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }
        
        // Check if exam is already taken
        $isCompleted = Grade::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->exists();
            
        if ($isCompleted) {
            return response()->json([
                'message' => 'You have already completed this exam',
                'exam' => $exam
            ]);
        }
        
        return response()->json([
            'exam' => $exam
        ]);
    }
    
    /**
     * Start an exam
     * POST /api/student/exams/:id/start
     */
    public function startExam($id)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }
        
        $exam = Exam::findOrFail($id);
        
        // Check if student is enrolled in the course
        $isEnrolled = $student->courses()
            ->where('courses.id', $exam->course_id)
            ->exists();
            
        if (!$isEnrolled) {
            return response()->json(['message' => 'You are not enrolled in this course'], 403);
        }
        
        // Check if exam is available to take
        $now = now();
        if ($now->lessThan($exam->exam_date)) {
            return response()->json(['message' => 'Exam has not started yet'], 403);
        }
        
        if ($now->greaterThan($exam->getEndTimeAttribute())) {
            return response()->json(['message' => 'Exam has already ended'], 403);
        }
        
        // Check if student has already started or completed the exam
        $hasStarted = Grade::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->exists();
            
        if ($hasStarted) {
            return response()->json(['message' => 'You have already started or completed this exam'], 403);
        }
        
        // Create an empty grade record to mark that the student has started the exam
        $grade = new Grade([
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'grade' => 0, // Will be updated when exam is submitted
            'graded_at' => null
        ]);
        $grade->save();
        
        // Get the exam questions with choices (but don't include which choice is correct)
        $questions = ExamQuestion::where('exam_id', $exam->id)
            ->with(['question' => function($query) {
                $query->with(['choice' => function($q) {
                    $q->select('id', 'question_id', 'choice_text'); // Exclude is_correct
                }]);
            }])
            ->get();
        
        return response()->json([
            'message' => 'Exam started successfully',
            'exam' => $exam,
            'questions' => $questions,
            'end_time' => $exam->getEndTimeAttribute()
        ]);
    }
    
    /**
     * Submit answer for a question
     * POST /api/student/exams/:examId/questions/:questionId/answer
     */
    public function submitAnswer(Request $request, $examId, $questionId)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }
        
        $exam = Exam::findOrFail($examId);
        $examQuestion = ExamQuestion::where('exam_id', $examId)
            ->where('question_id', $questionId)
            ->firstOrFail();
        
        // Check if exam is still in progress
        if (!$exam->getIsOngoingAttribute()) {
            return response()->json(['message' => 'Exam is not currently active'], 403);
        }
        
        // Validate the request
        $validated = $request->validate([
            'choice_id' => 'nullable|exists:choices,id',
            'written_answer' => 'nullable|string',
        ]);
        
        // Check if it's a multiple choice or written question
        $question = $examQuestion->question;
        $isMultipleChoice = $question->isMultipleChoice();
        
        // Save or update the student's answer
        $answer = StudentExamAnswer::updateOrCreate(
            [
                'student_id' => $student->id,
                'exam_question_id' => $examQuestion->id,
            ],
            [
                'written_answer' => $isMultipleChoice ? $validated['choice_id'] : $validated['written_answer'],
                'graded' => $isMultipleChoice, // Multiple choice questions are automatically graded
            ]
        );
        
        return response()->json([
            'message' => 'Answer submitted successfully',
            'answer' => $answer
        ]);
    }
    
    /**
     * Submit completed exam
     * POST /api/student/exams/:id/submit
     */
    public function submitExam($id)
    {
        $student = auth()->user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student record not found'], 404);
        }
        
        $exam = Exam::findOrFail($id);
        
        // Check if the student has started the exam
        $grade = Grade::where('student_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();
            
        if (!$grade) {
            return response()->json(['message' => 'You have not started this exam'], 404);
        }
        
        if ($grade->graded_at) {
            return response()->json(['message' => 'You have already submitted this exam'], 403);
        }
        
        // Get all exam questions
        $examQuestions = ExamQuestion::where('exam_id', $exam->id)->get();
        $totalPoints = $examQuestions->sum('weight');
        $earnedPoints = 0;
        
        // Calculate grade for multiple choice questions
        foreach ($examQuestions as $examQuestion) {
            $question = $examQuestion->question;
            
            // Get student's answer for this question
            $studentAnswer = StudentExamAnswer::where('student_id', $student->id)
                ->where('exam_question_id', $examQuestion->id)
                ->first();
                
            if ($studentAnswer && $question->isMultipleChoice()) {
                // Check if the answer is correct
                $correctChoiceId = $question->correctChoice()->id;
                if ($studentAnswer->written_answer == $correctChoiceId) {
                    $earnedPoints += $examQuestion->weight;
                }
            }
            
            // Written questions will be graded manually by the instructor
        }
        
        // Calculate preliminary grade (excluding written questions)
        $preliminaryGrade = ($totalPoints > 0) ? ($earnedPoints / $totalPoints) * 100 : 0;
        
        // Update the grade
        $grade->grade = $preliminaryGrade;
        $grade->graded_at = now();
        $grade->save();
        
        return response()->json([
            'message' => 'Exam submitted successfully',
            'preliminary_grade' => $preliminaryGrade,
            'note' => 'Written questions will be graded manually by the instructor'
        ]);
    }
}