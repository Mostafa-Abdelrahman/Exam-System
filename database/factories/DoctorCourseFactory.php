<?php

namespace Database\Factories;

use App\Models\DoctorCourse;
use App\Models\Doctor;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DoctorCourse>
 */
class DoctorCourseFactory extends Factory
{
    protected $model = DoctorCourse::class;

    public function definition(): array
    {
        return [
            'doctor_id' => Doctor::factory(),
            'course_id' => Course::factory(),
        ];
    }
}
