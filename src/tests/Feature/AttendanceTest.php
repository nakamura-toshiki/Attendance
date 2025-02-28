<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**日時取得 */
    public function test_displays_current_datetime()
    {
        $currentDateTime = now();

        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get(route('user.attendance'));

        $formattedDate = $currentDateTime->isoFormat('YYYY年MM月DD日(ddd)');
        $formattedTime = $currentDateTime->isoFormat('HH:mm');

        $response->assertStatus(200);
        $response->assertSee($formattedDate);
        $response->assertSee($formattedTime);
    }

    /**ステータス確認機能・出勤機能・休憩機能・退勤機能をまとめている */
    /**勤務外ステータス */
    public function test_display_status_off()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('user.index'));

        $response->assertStatus(200);
        $response->assertSee('勤務外');  /* ステータス表示 */
    }
    /**出勤機能および出勤中ステータス */
    public function test_display_status_working_and_clockIn_function()
    {
        $user = User::factory()->has(Attendance::factory())->create([
            'name' => 'test',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('user.index'));
        $response->assertSee('出勤');  /* ボタン表示 */

        $clockInTime = now()->format('H:i');
        $response = $this->post(route('user.attendance'), ['action' => '出勤', 'time' => $clockInTime]);

        $response = $this->get(route('user.index'));
        $response->assertSee('出勤中');  /* ステータス表示 */

        $response = $this->post(route('user.attendance'), ['action' => '退勤']);
        $this->actingAs($user);
        $response = $this->get(route('user.index'));
        $response->assertDontSee('出勤');  /* 出勤は一日一回 */

        $admin = User::first();
        $this->actingAs($admin, 'admin');
        $user = User::where('name', 'test')->first();
        $attendance = Attendance::where('user_id', $user->id)->first();

        $adminResponse = $this->get(route('admin.list'));
        $adminResponse->assertSee($user->name);
        $adminResponse->assertSee(\Carbon\Carbon::parse($attendance->date)->format('Y/m/d'));
        $adminResponse->assertSee($clockInTime);  /*管理画面での時刻表示 */
    }
    /**退勤機能および退勤済みステータス */
    public function test_display_status_leftWork_and_clockOut_function()
    {
        $user = User::factory()->has(Attendance::factory())->create([
            'name' => 'test',
        ]);

        $this->actingAs($user);
        $response = $this->post(route('user.attendance'), ['action' => '出勤']);

        $this->actingAs($user);
        $response = $this->get(route('user.index'));
        $response->assertSee('退勤');  /* ボタン表示 */

        $clockOutTime = now()->format('H:i');
        $response = $this->post(route('user.attendance'), ['action' => '退勤', 'time' => $clockOutTime]);

        $response = $this->get(route('user.index'));
        $response->assertSee('退勤済');  /* ステータス表示 */

        $admin = User::first();
        $this->actingAs($admin, 'admin');
        $user = User::where('name', 'test')->first();
        $attendance = Attendance::where('user_id', $user->id)->first();

        $adminResponse = $this->get(route('admin.list'));
        $adminResponse->assertSee($user->name);
        $adminResponse->assertSee(\Carbon\Carbon::parse($attendance->date)->format('Y/m/d'));
        $adminResponse->assertSee($clockOutTime);  /*管理画面での時刻表示 */
    }
    /**休憩機能および休憩中ステータス */
    public function test_display_status_break_and_break_function()
    {
        $user = User::factory()->has(Attendance::factory())->create([
            'name' => 'test',
        ]);

        $this->actingAs($user);
        $response = $this->post(route('user.attendance'), ['action' => '出勤']);

        $this->actingAs($user);
        $response = $this->get(route('user.index'));
        $response->assertSee('休憩入');  /* ボタン表示 */

        $breakStartTime = now()->format('H:i');
        $response = $this->post(route('user.attendance'), ['action' => '休憩入', 'time' =>$breakStartTime]);

        $response = $this->get(route('user.index'));
        $response->assertSee('休憩中');  /* ステータス表示 */


        $this->actingAs($user);
        $response = $this->get(route('user.index'));
        $response->assertSee('休憩戻');  /* ボタン表示 */

        $breakEndTime = now()->format('H:i');
        $response = $this->post(route('user.attendance'), ['action' => '休憩戻', 'time' => $breakEndTime]);

        $response = $this->get(route('user.index'));
        $response->assertSee('出勤中');  /* ステータス表示 */
        $response->assertSee('休憩入');  /* 休憩何度でもできる */

        $response = $this->post(route('user.attendance'), ['action' => '休憩入']);
        $response = $this->get(route('user.index'));
        $response->assertSee('休憩戻');  /* 休憩何度でもできる */

        $breakDuration = \Carbon\Carbon::parse($breakStartTime)->diff(\Carbon\Carbon::parse($breakEndTime));
        $formattedBreakTime = ltrim($breakDuration->format('%H:%I'), '0');

        $admin = User::first();
        $this->actingAs($admin, 'admin');
        $user = User::where('name', 'test')->first();
        $attendance = Attendance::where('user_id', $user->id)->first();

        $adminResponse = $this->get(route('admin.list'));
        $adminResponse->assertSee($user->name);
        $adminResponse->assertSee(\Carbon\Carbon::parse($attendance->date)->format('Y/m/d'));
        $adminResponse->assertSee($formattedBreakTime);  /*管理画面での時刻表示 */
    }
}


