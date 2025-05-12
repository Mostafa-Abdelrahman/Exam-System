<?php

namespace Database\Seeders;

use App\Models\MajorCourse;
use Illuminate\Database\Seeder;

class MajorCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MajorCourse::factory()->count(10)->create();
    }
}
