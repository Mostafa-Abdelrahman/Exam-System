<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exam;
use App\Models\User;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DashboardController extends Controller
{
    /**
     * Get statistics for admin dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        $stats = [
            'data' => [
                'users' => [
                    'total' => User::count(),
                    'admins' => User::where('role', 'admin')->count(),
                    'doctors' => User::where('role', 'doctor')->count(),
                    'students' => User::where('role', 'student')->count(),
                ],
                'courses' => [
                    'total' => Course::count(),
                ],
                'majors' => [
                    'total' => Major::count(), // Assuming this is a static value or calculated differently
                ],
                'exams' => [
                    'total' => Exam::count(),
                    'published' => Exam::where('status', 'published')->count(),
                    'draft' => Exam::where('status', 'draft')->count(),
                ],
            ]
        ];

        return response()->json($stats);
    }
}