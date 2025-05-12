<?php

namespace Database\Seeders;

use App\Models\DoctorCourse;
use Illuminate\Database\Seeder;

class DoctorCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DoctorCourse::factory()->count(50)->create();
    }
}
