<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminStaffTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_display_users_info()
    {
        /* 全ユーザーの情報 */
        $users = User::factory()->count(3)->create();

        $admin = User::first();
        $this->actingAs($admin, 'admin');

        $response = $this->get(route('admin.staff'));
        $response->assertStatus(200);

        foreach($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }
        /* ユーザーの勤怠一覧情報 */
        foreach ($users as $user) {
            $attendances = [];
            for ($i = 0; $i < 3; $i++) {
                $attendances[] = Attendance::create([
                    'user_id' => $user->id,
                    'date' => now()->subDays($i)->toDateString(),
                    'clock_in' => now()->subDays($i)->format('H:i'),
                    'clock_out' => now()->subDays($i)->addHours(8)->format('H:i'),
                    'break_time' => '1:00',
                ]);
            }

            $response = $this->get(route('admin.person', ['user_id' => $user->id]));
            foreach ($attendances as $attendance) {
                $response->assertSee(\Carbon\Carbon::parse($attendance->date)->isoFormat('MM/DD(ddd)'));
                $response->assertSee($attendance->clock_in);
                $response->assertSee($attendance->clock_out);
                $response->assertSee($attendance->break_time);
                $response->assertSee($attendance->total_work_time);
            }
        }
    }

    public function test_display_list_page_and_operation_check()
    {
        $admin = User::first();
        $this->actingAs($admin, 'admin');

        $user = User::factory()->create();

        $response = $this->get(route('admin.person', ['user_id' => $user->id]));

        /* 前月の情報 */
        $attendancePreviousMonth = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->subMonth()->format('Y-m-d'),
            'clock_in' => now()->subMonth()->format('H:i'),
            'clock_out' => now()->subMonth()->addHours(8)->format('H:i'),
            'break_time' => '1:00',
        ]);

        $previousMonth = now()->subMonth()->isoFormat('YYYY/MM');

        $response = $this->get(route('admin.person', ['user_id' => $user->id, 'date' => now()->subMonth()->format('Y-m')]));

        $response->assertSee($previousMonth);
        $response->assertSee(\Carbon\Carbon::parse($attendancePreviousMonth->date)->isoFormat('MM/DD(ddd)'));
        $response->assertSee($attendancePreviousMonth->clock_in);
        $response->assertSee($attendancePreviousMonth->clock_out);
        $response->assertSee($attendancePreviousMonth->break_time);
        $response->assertSee($attendancePreviousMonth->total_work_time);
        /* 翌月の情報 */
        $response = $this->get(route('admin.person', ['user_id' => $user->id]));

        $attendanceNextMonth = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->addMonth()->format('Y-m-d'),
            'clock_in' => now()->addMonth()->format('H:i'),
            'clock_out' => now()->addMonth()->addHours(8)->format('H:i'),
            'break_time' => '1:00',
        ]);

        $nextMonth = now()->addMonth()->isoFormat('YYYY/MM');

        $response = $this->get(route('admin.person', ['user_id' => $user->id, 'date' => now()->addMonth()->format('Y-m')]));

        $response->assertSee($nextMonth);
        $response->assertSee(\Carbon\Carbon::parse($attendanceNextMonth->date)->isoFormat('MM/DD(ddd)'));
        $response->assertSee($attendanceNextMonth->clock_in);
        $response->assertSee($attendanceNextMonth->clock_out);
        $response->assertSee($attendanceNextMonth->break_time);
        $response->assertSee($attendanceNextMonth->total_work_time);
        /* 勤怠詳細 */
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
        ]);
        $response = $this->get(route('admin.person', ['user_id' => $user->id]));

        $response->assertSee('詳細');

        $response = $this->get(route('user.show', ['attendance_id' => $attendance->id]));

        $response->assertStatus(200);
    }

}
