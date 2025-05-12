<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\AssignmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin API Routes
|--------------------------------------------------------------------------
*/


// Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // Dashboard Statistics
    Route::get('/stats', [DashboardController::class, 'getStats']);

    // User Management
    Route::get('/admin/users', [UserController::class, 'index']);
    Route::post('/admin/users', [UserController::class, 'store']);
    Route::put('/admin/users/{id}', [UserController::class, 'update']);
    Route::delete('/admin/users/{id}', [UserController::class, 'destroy']);
    
    // Course Management
    Route::get('/admin/courses', [CourseController::class, 'index']);
    Route::post('/admin/courses', [CourseController::class, 'store']);
    Route::put('/admin/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/admin/courses/{id}', [CourseController::class, 'destroy']);
    
    // Course Assignments
    Route::post('/admin/assignments/doctors/{doctorId}/courses/{courseId}', [AssignmentController::class, 'assignDoctorToCourse']);
    Route::delete('/admin/assignments/doctors/{doctorId}/courses/{courseId}', [AssignmentController::class, 'removeDoctorFromCourse']);
    Route::post('/admin/assignments/students/{studentId}/courses/{courseId}', [AssignmentController::class, 'enrollStudentInCourse']);
    Route::delete('/admin/assignments/students/{studentId}/courses/{courseId}', [AssignmentController::class, 'removeStudentFromCourse']);
// });