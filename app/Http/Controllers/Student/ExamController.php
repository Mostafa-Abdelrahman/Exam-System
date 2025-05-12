<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\Question;
use App\Models\StudentExam;
use App\Models\Answer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExamController extends Controller
{
    /**
     * Get upcoming exams for the student
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUpcoming()
    {
        $student = Auth::user();
        
        $exams = Exam::whereHas('course.students', function ($query) use ($student) {
                $query->where('users.id', $student->id);
            })
            ->where('exam_date', '>', Carbon::now())
            ->where('status', 'published')
            ->with('course:id,name,code')
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'course_id' => $exam->course_id,
                    'exam_date' => $exam->exam_date,
                    'duration' => $exam->duration,
                    'instructions' => $exam->instructions,
                    'status' => $exam->status,
                    'course' => [
                        'name' => $exam->course->name,
                        'code' => $exam->course->code
                    ]
                ];
            });
        
        return response()->json([
            'data' => $exams
        ]);
    }

    /**
     * Get available exams for the student
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailable()
    {
        $student = Auth::user();
        $now = Carbon::now();
        
        $exams = Exam::whereHas('course.students', function ($query) use ($student) {
                $query->where('users.id', $student->id);
            })
            ->where('status', 'published')
            ->where('exam_date', '<=', $now)
            ->where(DB::raw("ADDTIME(exam_date, CONCAT(duration, ':00'))"), '>=', $now)
            ->whereDoesntHave('studentExams', function ($query) use ($student) {
                $query->where('user_id', $student->id)
                      ->where('submitted_at', '!=', null);
            })
            ->with('course:id,name,code')
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'course_id' => $exam->course_id,
                    'exam_date' => $exam->exam_date,
                    'duration' => $exam->duration,
                    'instructions' => $exam->instructions,
                    'status' => $exam->status,
                    'course' => [
                        'name' => $exam->course->name,
                        'code' => $exam->course->code
                    ]
                ];
            });
        
        return response()->json([
            'data' => $exams
        ]);
    }

    /**
     * Get details of a specific exam
     *
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $student = Auth::user();
        
        $exam = Exam::whereHas('course.students', function ($query) use ($student) {
                $query->where('users.id', $student->id);
            })
            ->where('id', $id)
            ->with(['questions.choices' => function($query) {
                $query->select('id', 'question_id', 'text');
            }])
            ->firstOrFail();
        
        $examData = [
            'id' => $exam->id,
            'name' => $exam->name,
            'course_id' => $exam->course_id,
            'exam_date' => $exam->exam_date,
            'duration' => $exam->duration,
            'instructions' => $exam->instructions,
            'status' => $exam->status,
            'questions' => $exam->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'text' => $question->text,
                    'type' => $question->type,
                    'choices' => $question->type === 'mcq' ? $question->choices->map(function ($choice) {
                        return [
                            'id' => $choice->id,
                            'text' => $choice->text
                        ];
                    }) : []
                ];
            })
        ];
        
        return response()->json([
            'data' => $examData
        ]);
    }

    /**
     * Start an exam
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function start(Request $request, $id)
    {
        $student = Auth::user();
        
        // Check if exam is available
        $exam = Exam::whereHas('course.students', function ($query) use ($student) {
                $query->where('users.id', $student->id);
            })
            ->where('id', $id)
            ->where('status', 'published')
            ->firstOrFail();
        
        // Check if the student already started this exam
        $existingExam = StudentExam::where('user_id', $student->id)
            ->where('exam_id', $exam->id)
            ->first();
            
        if ($existingExam && $existingExam->submitted_at) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted this exam'
            ], 403);
        }
        
        if ($existingExam) {
            return response()->json([
                'data' => [
                    'student_exam_id' => $existingExam->id,
                    'start_time' => $existingExam->started_at,
                    'end_time' => Carbon::parse($existingExam->started_at)->addMinutes($exam->duration)
                ]
            ]);
        }
        
        // Create student exam record
        $startTime = Carbon::now();
        $endTime = Carbon::now()->addMinutes($exam->duration);
        
        $studentExam = StudentExam::create([
            'user_id' => $student->id,
            'exam_id' => $exam->id,
            'started_at' => $startTime
        ]);
        
        return response()->json([
            'data' => [
                'student_exam_id' => $studentExam->id,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]
        ]);
    }

    /**
     * Submit an answer for a question
     *
     * @param Request $request
     * @param string $examId
     * @param string $questionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitAnswer(Request $request, $examId, $questionId)
    {
        $request->validate([
            'answer' => 'required',
            'student_exam_id' => 'required|uuid'
        ]);
        
        $studentExam = StudentExam::where('id', $request->student_exam_id)
            ->where('user_id', Auth::id())
            ->where('exam_id', $examId)
            ->whereNull('submitted_at')
            ->firstOrFail();
        
        $question = Question::where('id', $questionId)
            ->whereHas('exams', function($query) use ($examId) {
                $query->where('exams.id', $examId);
            })
            ->firstOrFail();
        
        // Check if answer already exists and update it
        $answer = Answer::updateOrCreate(
            [
                'student_exam_id' => $studentExam->id,
                'question_id' => $questionId
            ],
            [
                'answer' => $request->answer
            ]
        );
        
        return response()->json([
            'success' => true,
            'message' => 'Answer submitted successfully'
        ]);
    }

    /**
     * Submit a completed exam
     *
     * @param Request $request
     * @param string $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submit(Request $request, $id)
    {
        $request->validate([
            'student_exam_id' => 'required|uuid'
        ]);
        
        $studentExam = StudentExam::where('id', $request->student_exam_id)
            ->where('user_id', Auth::id())
            ->where('exam_id', $id)
            ->whereNull('submitted_at')
            ->firstOrFail();
        
        // Auto-grade MCQ questions
        $exam = Exam::with(['questions' => function($query) {
            $query->where('type', 'mcq');
        }])->find($id);
        
        $totalScore = 0;
        
        foreach ($exam->questions as $question) {
            if ($question->type === 'mcq') {
                $answer = Answer::where('student_exam_id', $studentExam->id)
                    ->where('question_id', $question->id)
                    ->first();
                
                if ($answer) {
                    $correctChoice = $question->choices()
                        ->where('is_correct', true)
                        ->first();
                    
                    if ($correctChoice && $answer->answer === $correctChoice->id) {
                        $weight = DB::table('exam_question')
                            ->where('exam_id', $id)
                            ->where('question_id', $question->id)
                            ->value('weight') ?? 1;
                        
                        $answer->update([
                            'score' => $weight,
                            'graded' => true
                        ]);
                        
                        $totalScore += $weight;
                    } else {
                        $answer->update([
                            'score' => 0,
                            'graded' => true
                        ]);
                    }
                }
            }
        }
        
        // Update student exam record
        $studentExam->update([
            'submitted_at' => Carbon::now(),
            'score' => $totalScore
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Exam submitted successfully'
        ]);
    }
}