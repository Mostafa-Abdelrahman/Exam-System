<?php

namespace Database\Factories;

use App\Models\Major;
use App\Models\Course;
use App\Models\MajorCourse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MajorCourse>
 */
class MajorCourseFactory extends Factory
{
    protected $model = MajorCourse::class;

    public function definition(): array
    {
        return [
            'major_id' => Major::factory(),
            'course_id' => Course::factory(),
        ];
    }
}
