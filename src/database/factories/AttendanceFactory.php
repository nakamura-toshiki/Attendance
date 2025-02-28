<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => null,
            'date' => Carbon::today()->toDateString(),
            'clock_in' => null,
            'clock_out' => null,
            'break_time' => null,
            'description' => null,
            'status' => '勤務外',
        ];
    }
}
