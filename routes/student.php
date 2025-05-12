<?php

use App\Http\Controllers\Student\CourseController;
use App\Http\Controllers\Student\ExamController;
use App\Http\Controllers\Student\ResultController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Student API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:student'])->group(function () {
    // Courses
    Route::get('/courses', [CourseController::class, 'index']);
    
    // Exams
    Route::get('/exams/upcoming', [ExamController::class, 'getUpcoming']);
    Route::get('/exams/available', [ExamController::class, 'getAvailable']);
    Route::get('/exams/{id}', [ExamController::class, 'show']);
    Route::post('/exams/{id}/start', [ExamController::class, 'start']);
    Route::post('/exams/{examId}/questions/{questionId}/answer', [ExamController::class, 'submitAnswer']);
    Route::post('/exams/{id}/submit', [ExamController::class, 'submit']);
    
    // Results
    Route::get('/results', [ResultController::class, 'index']);
    Route::get('/results/{examId}', [ResultController::class, 'show']);
});