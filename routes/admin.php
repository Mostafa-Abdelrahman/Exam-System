<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AssignmentController;

Route::prefix('admin')->group(function () {

    // ---------------------- Stats ----------------------
    Route::get('/stats', [StatsController::class, 'index']);

    // ---------------------- Exams ----------------------
    Route::get('/exams', [ExamController::class, 'index']);
    Route::get('/exams/{id}', [ExamController::class, 'show']);
    Route::patch('/exams/{id}/status', [ExamController::class, 'updateStatus']);
    Route::delete('/exams/{id}', [ExamController::class, 'destroy']);
    Route::get('/exams/{id}/results', [ExamController::class, 'getResults']);

    // ---------------------- Courses ----------------------
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses/{id}', [CourseController::class, 'show']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

    // ---------------------- Users ----------------------
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // ---------------------- Assignments ----------------------
    Route::post('/doctors/{doctorId}/courses/{courseId}', [AssignmentController::class, 'assignDoctorToCourse']);
    Route::delete('/doctors/{doctorId}/courses/{courseId}', [AssignmentController::class, 'removeDoctorFromCourse']);

    Route::post('/students/{studentId}/courses/{courseId}', [AssignmentController::class, 'enrollStudentInCourse']);
    Route::delete('/students/{studentId}/courses/{courseId}', [AssignmentController::class, 'removeStudentFromCourse']);
});
