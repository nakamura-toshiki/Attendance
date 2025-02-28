<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminListTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }
    /** 全ユーザーの勤怠情報 */
    public function test_display_all_users_attendance_info()
    {
        $users = User::factory()->count(3)->create();
        
        $today = now()->toDateString();

        foreach ($users as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in' => now()->format('H:i'),
                'clock_out' => now()->addHours(8)->format('H:i'),
                'break_time' => '1:00',
            ]);
        }

        $admin = User::first();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.list'));

        foreach ($users as $user) {
            $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

            $response->assertSee($user->name);
            $response->assertSee($attendance->clock_in);
            $response->assertSee($attendance->clock_out);
            $response->assertSee($attendance->break_time);
            $response->assertSee($attendance->total_work_time);
        }
    }
    /**勤怠一覧ページ動作 */
    public function test_display_all_users_list_page_and_operation_check()
    {
        $users = User::factory()->count(3)->create();

        $admin = User::first();
        $this->actingAs($admin, 'admin');
        /* 現在 */
        $current = now()->isoFormat('YYYY/MM/DD');
        foreach ($users as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'date' => now()->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_time' => '1:00',
            ]);
        }

        $response = $this->get(route('admin.list'));
        $response->assertSee($current);
        /* 前日の情報 */
        $previousDay = now()->subDay()->isoFormat('YYYY/MM/DD');
        foreach ($users as $user) {
            $attendancePreviousDay = Attendance::create([
                'user_id' => $user->id,
                'date' => now()->subDay()->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_time' => '1:00',
            ]);

            $response = $this->get(route('admin.list', ['date' => now()->subDay()->format('Y-m-d')]));
            $response->assertSee($previousDay);
            $response->assertSee($user->name);
            $response->assertSee($attendancePreviousDay->clock_in);
            $response->assertSee($attendancePreviousDay->clock_out);
            $response->assertSee($attendancePreviousDay->break_time);
            $response->assertSee($attendancePreviousDay->total_work_time);
        }
        /* 翌日の情報 */
        $nextDay = now()->addDay()->isoFormat('YYYY/MM/DD');
        foreach ($users as $user) {
            $attendanceNextDay = Attendance::create([
                'user_id' => $user->id,
                'date' => now()->addDay()->format('Y-m-d'),
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'break_time' => '1:00',
            ]);

            $response = $this->get(route('admin.list', ['date' => now()->addDay()->format('Y-m-d')]));
            $response->assertSee($nextDay);
            $response->assertSee($user->name);
            $response->assertSee($attendanceNextDay->clock_in);
            $response->assertSee($attendanceNextDay->clock_out);
            $response->assertSee($attendanceNextDay->break_time);
            $response->assertSee($attendanceNextDay->total_work_time);
        }
    }
}
