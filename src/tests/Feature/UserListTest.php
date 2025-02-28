<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class UserListTest extends TestCase
{
    use DatabaseMigrations;
    /** 勤怠情報一覧 */
    public function test_display_user_attendance_info()
    {
        $user = User::factory()->create();
        $attendance1 = Attendance::where('user_id', $user->id)->first();
        $attendance1->update([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'clock_in' => now()->format('H:i'),
            'clock_out' => now()->addHours(8)->format('H:i'),
            'break_time' => '1:00',
        ]);

        $attendance2 = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->subDay()->toDateString(),
            'clock_in' => now()->subDay()->format('H:i'),
            'clock_out' => now()->subDay()->addHours(8)->format('H:i'),
            'break_time' => '1:00',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('user.list'));

        $response->assertSee(\Carbon\Carbon::parse($attendance1->date)->isoFormat('MM/DD(ddd)'));
        $response->assertSee($attendance1->clock_in);
        $response->assertSee($attendance1->clock_out);
        $response->assertSee($attendance1->break_time);
        $response->assertSee($attendance1->total_work_time);

        $response->assertSee(\Carbon\Carbon::parse($attendance2->date)->isoFormat('MM/DD(ddd)'));
        $response->assertSee($attendance2->clock_in);
        $response->assertSee($attendance2->clock_out);
        $response->assertSee($attendance1->break_time);
        $response->assertSee($attendance1->total_work_time);
    }
/**勤怠一覧ページ動作 */
    public function test_display_list_page_and_operation_check()
    {
        /* 現在 */
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('user.list'));

        $currentMonth = now()->isoFormat('YYYY/MM');

        $response->assertSee($currentMonth);
        /* 前月の情報 */
        $attendancePreviousMonth = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->subMonth()->format('Y-m-d'),
            'clock_in' => now()->subMonth()->format('H:i'),
            'clock_out' => now()->subMonth()->addHours(8)->format('H:i'),
            'break_time' => '1:00',
        ]);

        $previousMonth = now()->subMonth()->isoFormat('YYYY/MM');

        $response = $this->get(route('user.list', ['date' => now()->subMonth()->format('Y-m')]));

        $response->assertSee($previousMonth);
        $response->assertSee(\Carbon\Carbon::parse($attendancePreviousMonth->date)->isoFormat('MM/DD(ddd)'));
        $response->assertSee($attendancePreviousMonth->clock_in);
        $response->assertSee($attendancePreviousMonth->clock_out);
        $response->assertSee($attendancePreviousMonth->break_time);
        $response->assertSee($attendancePreviousMonth->total_work_time);
        /* 翌月の情報 */
        $response = $this->get(route('user.list'));

        $attendanceNextMonth = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->addMonth()->format('Y-m-d'),
            'clock_in' => now()->addMonth()->format('H:i'),
            'clock_out' => now()->addMonth()->addHours(8)->format('H:i'),
            'break_time' => '1:00',
        ]);

        $nextMonth = now()->addMonth()->isoFormat('YYYY/MM');

        $response = $this->get(route('user.list', ['date' => now()->addMonth()->format('Y-m')]));

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
        $this->actingAs($user);
        $response = $this->get(route('user.list'));

        $response->assertSee('詳細');

        $response = $this->get(route('user.show', ['attendance_id' => $attendance->id]));

        $response->assertStatus(200);
    }
}
