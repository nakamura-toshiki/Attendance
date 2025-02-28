<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class AttendanceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('id', 2)->first();

        $param = [
            'user_id' => $user->id,
            'date' => Carbon::now()->subDays(2)->toDateString(),
            'clock_in' => Carbon::createFromTime(8, 0, 0)->toTimeString(),
            'clock_out' => Carbon::createFromTime(17, 0, 0)->toTimeString(),
            'break_time' => Carbon::createFromTime(1, 0, 0)->toTimeString(),
            'status' => '退勤済',
        ];
        DB::table('attendances')->insert($param);

        $param = [
            'user_id' => $user->id,
            'date' => Carbon::now()->subDays()->toDateString(),
            'clock_in' => Carbon::createFromTime(9, 0, 0)->toTimeString(),
            'clock_out' => Carbon::createFromTime(18, 0, 0)->toTimeString(),
            'break_time' => Carbon::createFromTime(1, 0, 0)->toTimeString(),
            'status' => '退勤済',
        ];
        DB::table('attendances')->insert($param);
    }
}
