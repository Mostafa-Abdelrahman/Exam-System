<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Doctor;
use App\Models\Major;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display a listing of all users.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::query();
        
        // Filter by role if provided
        if ($request->has('role')) {
            $users->where('role', $request->role);
        }
        
        // Include related data based on role
        $users->when($request->has('with_details'), function($query) {
            return $query->with(['student.major', 'doctor.major']);
        });
        
        // Pagination
        $users = $users->paginate($request->per_page ?? 15);
        
        return response()->json([
            'users' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    /**
     * Store a newly created user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'gender' => 'required|in:male,female,other',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,doctor,student',
            'major_id' => 'required_if:role,doctor,student|exists:majors,major_id',
            'specialization' => 'required_if:role,doctor|nullable|string|max:255',
        ]);
        
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Create the base user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'gender' => $validated['gender'],
                'hashed_password' => Hash::make($validated['password']),
                'role' => $validated['role'],
            ]);
            
            // Create role-specific record
            if ($validated['role'] === 'student') {
                Student::create([
                    'user_id' => $user->user_id,
                    'major_id' => $validated['major_id'],
                ]);
            } elseif ($validated['role'] === 'doctor') {
                Doctor::create([
                    'user_id' => $user->user_id,
                    'major_id' => $validated['major_id'],
                    'specilazation' => $validated['specialization'],
                ]);
            }
            
            DB::commit();
            
            // Load relationship data
            $user->load($validated['role'] === 'student' ? 'student.major' : 'doctor.major');
            
            return response()->json([
                'user' => $user,
                'message' => 'User created successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error creating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        
        // Load appropriate relationships based on role
        if ($user->role === 'student') {
            $user->load(['student.major', 'student.courses']);
        } elseif ($user->role === 'doctor') {
            $user->load(['doctor.major', 'doctor.courses']);
        }
        
        return response()->json([
            'user' => $user,
            'message' => 'User details retrieved successfully'
        ]);
    }

    /**
     * Update the specified user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($id, 'user_id'),
            ],
            'gender' => 'sometimes|in:male,female,other',
            'password' => 'sometimes|string|min:8',
            'major_id' => 'sometimes|exists:majors,major_id',
            'specialization' => 'sometimes|nullable|string|max:255',
        ]);
        
        // Start transaction
        DB::beginTransaction();
        
        try {
            // Update password only if provided
            if (isset($validated['password'])) {
                $validated['hashed_password'] = Hash::make($validated['password']);
                unset($validated['password']);
            }
            
            // Update the user record
            $user->update($validated);
            
            // Update role specific details
            if ($user->role === 'student' && isset($validated['major_id'])) {
                $student = Student::where('user_id', $user->user_id)->first();
                if ($student) {
                    $student->update(['major_id' => $validated['major_id']]);
                }
            } elseif ($user->role === 'doctor') {
                $doctor = Doctor::where('user_id', $user->user_id)->first();
                if ($doctor) {
                    $updates = [];
                    if (isset($validated['major_id'])) {
                        $updates['major_id'] = $validated['major_id'];
                    }
                    if (isset($validated['specialization'])) {
                        $updates['specilazation'] = $validated['specialization'];
                    }
                    if (!empty($updates)) {
                        $doctor->update($updates);
                    }
                }
            }
            
            DB::commit();
            
            // Reload the user with fresh data
            $user = User::findOrFail($id);
            
            // Load appropriate relationships based on role
            if ($user->role === 'student') {
                $user->load('student.major');
            } elseif ($user->role === 'doctor') {
                $user->load('doctor.major');
            }
            
            return response()->json([
                'user' => $user,
                'message' => 'User updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error updating user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Due to cascading deletes set up in the database,
        // this will also remove related records in Students/Doctors tables
        $user->delete();
        
        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}