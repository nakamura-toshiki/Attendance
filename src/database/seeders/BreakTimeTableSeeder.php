<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Attendance;
use Carbon\Carbon;

class BreakTimeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendance1 = Attendance::first();
        $attendance2 = Attendance::where('id', 2)->first();

        $param = [
            'attendance_id' => $attendance1->id,
            'break_start' => Carbon::createFromTime(12, 0, 0)->toTimeString(),
            'break_end' => Carbon::createFromTime(13, 0, 0)->toTimeString(),
        ];
        DB::table('break_times')->insert($param);

        $param = [
            'attendance_id' => $attendance2->id,
            'break_start' => Carbon::createFromTime(12, 30, 0)->toTimeString(),
            'break_end' => Carbon::createFromTime(13, 30, 0)->toTimeString(),
        ];
        DB::table('break_times')->insert($param);
    }
}
