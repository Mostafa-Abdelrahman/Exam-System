<?php

use App\Http\Controllers\Doctor\CourseController;
use App\Http\Controllers\Doctor\ExamController;
use App\Http\Controllers\Doctor\QuestionController;
use App\Http\Controllers\Doctor\ChoiceController;
use App\Http\Controllers\Doctor\GradingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Doctor API Routes
|--------------------------------------------------------------------------
*/

// Route::middleware(['auth:sanctum', 'role:doctor'])->group(function () {
    // Courses
    Route::get('/doctor/courses', [CourseController::class, 'index']);
    Route::get('/courses/{id}/students', [CourseController::class, 'getStudents']);
    
    // Questions
    Route::get('/doctor/questions', [QuestionController::class, 'index']);
    Route::post('/doctor/questions', [QuestionController::class, 'store']);
    Route::put('/doctor/questions/{id}', [QuestionController::class, 'update']);
    Route::delete('/doctor/questions/{id}', [QuestionController::class, 'destroy']);
    
    // Question Choices
    Route::get('/doctor/questions/{questionId}/choices', [ChoiceController::class, 'index']);
    Route::post('/doctor/questions/{questionId}/choices', [ChoiceController::class, 'store']);
    Route::put('/doctor/choices/{id}', [ChoiceController::class, 'update']);
    Route::delete('/doctor/choices/{id}', [ChoiceController::class, 'destroy']);
    
    // Exams
    Route::get('/doctor/exams', [ExamController::class, 'index']);
    Route::post('/doctor/exams', [ExamController::class, 'store']);
    Route::put('/doctor/exams/{id}', [ExamController::class, 'update']);
    Route::delete('/doctor/exams/{id}', [ExamController::class, 'destroy']);
    
    // Exam Questions
    Route::get('/doctor/exams/{id}/questions', [ExamController::class, 'getQuestions']);
    Route::post('/doctor/exams/{examId}/questions', [ExamController::class, 'addQuestion']);
    Route::delete('/doctor/exam-questions/{id}', [ExamController::class, 'removeQuestion']);
    
    // Results and Grading
    Route::get('/exams/{id}/results', [GradingController::class, 'getExamResults']);
    Route::post('/answers/{id}/grade', [GradingController::class, 'gradeAnswer']);
    Route::post('/exams/{examId}/student/{studentId}/grade', [GradingController::class, 'assignFinalGrade']);
// });