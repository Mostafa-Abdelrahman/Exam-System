<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentCourseController;
use App\Http\Controllers\Student\StudentExamController;
use App\Http\Controllers\Student\StudentResultController;

/*
|--------------------------------------------------------------------------
| API Routes - Student
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'role:student'])->prefix('student')->group(function () {
    // Courses
    Route::get('/courses', [StudentCourseController::class, 'getEnrolledCourses']);
    
    // Exams
    Route::get('/exams/upcoming', [StudentExamController::class, 'getUpcomingExams']);
    Route::get('/exams/available', [StudentExamController::class, 'getAvailableExams']);
    Route::get('/exams/{id}', [StudentExamController::class, 'getExamDetails']);
    Route::post('/exams/{id}/start', [StudentExamController::class, 'startExam']);
    Route::post('/exams/{examId}/questions/{questionId}/answer', [StudentExamController::class, 'submitAnswer']);
    Route::post('/exams/{id}/submit', [StudentExamController::class, 'submitExam']);
    
    // Results
    Route::get('/results', [StudentResultController::class, 'getAllResults']);
    Route::get('/results/{examId}', [StudentResultController::class, 'getExamResult']);
});