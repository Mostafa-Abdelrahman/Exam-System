<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json(['message' => 'The server is UP and API is working!']);
});

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication Routes
require __DIR__.'/auth.php';

// Student Routes
require __DIR__.'/student.php';

// Doctor Routes
require __DIR__.'/doctor.php';

// Admin Routes
require __DIR__.'/admin.php';