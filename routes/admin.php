<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\StatsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AssignmentController;

/*
|--------------------------------------------------------------------------
| API Routes - Admin
|--------------------------------------------------------------------------
*/


Route::prefix('admin')
    // ->middleware(['auth', 'verified', 'admin'])
    ->group(function () {

    // Dashboard and Stats
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/stats', [StatsController::class, 'index'])
        ->name('admin.stats');

    // User Management
    Route::prefix('users')->name('admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Exam Management
    Route::prefix('exams')->name('admin.exams.')->group(function () {
        Route::get('/', [ExamController::class, 'index'])->name('index');
        Route::get('/{id}', [ExamController::class, 'show'])->name('show');
        Route::patch('/{id}/status', [ExamController::class, 'updateStatus'])->name('status');
        Route::delete('/{id}', [ExamController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/results', [ExamController::class, 'getResults'])->name('results');
    });

    // Course Management
    Route::prefix('courses')->name('admin.courses.')->group(function () {
        Route::get('/', [CourseController::class, 'index'])->name('index');
        Route::post('/', [CourseController::class, 'store'])->name('store');
        Route::get('/{id}', [CourseController::class, 'show'])->name('show');
        Route::put('/{id}', [CourseController::class, 'update'])->name('update');
        Route::delete('/{id}', [CourseController::class, 'destroy'])->name('destroy');
    });

    // Assignment Management
    Route::prefix('assignments')->name('admin.assignments.')->group(function () {
        // Doctor Assignments
        Route::prefix('doctors')->name('doctors.')->group(function () {
            Route::post('/{doctorId}/courses/{courseId}', [AssignmentController::class, 'assignDoctorToCourse'])
                ->name('assign');
            Route::delete('/{doctorId}/courses/{courseId}', [AssignmentController::class, 'removeDoctorFromCourse'])
                ->name('remove');
        });

        // Student Enrollments
        Route::prefix('students')->name('students.')->group(function () {
            Route::post('/{studentId}/courses/{courseId}', [AssignmentController::class, 'enrollStudentInCourse'])
                ->name('enroll');
            Route::delete('/{studentId}/courses/{courseId}', [AssignmentController::class, 'removeStudentFromCourse'])
                ->name('remove');
        });
    });
});
