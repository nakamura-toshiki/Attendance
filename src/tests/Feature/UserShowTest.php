<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;
use App\Models\UserRequest;

class UserShowTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }
    /**勤怠詳細ページ */
    /**表示内容が打刻と一致 */
    public function test_user_can_see_correct_attendance_details()
    {
        $user = User::factory()->create([
            'name' => 'testUser',
        ]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);
        $breakTime = BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('user.show', ['attendance_id' => $attendance->id]));
        $response->assertSee($user->name);
        $response->assertSee(now()->format('Y年'));
        $response->assertSee(now()->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
    /**バリデーション */
    public function test_invalid_fields_in_attendance_form()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);

        $response = $this->post(route('user.edit', ['attendance_id' => $attendance->id]), [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
        ]);

        $response->assertSessionHasErrors(['clock_out' => '出勤時間もしくは退勤時間が不適切な値です']);
        $response->assertRedirect();

        $response = $this->post(route('user.edit', ['attendance_id' => $attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'break_start' => '19:00',
                    'break_end' => '19:30',
                ]
            ],
        ]);

        $response->assertSessionHasErrors(['break_error' => '休憩時間が勤務時間外です']);

        $response = $this->post(route('user.edit', ['attendance_id' => $attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'breaks' => [
                [
                    'break_start' => '12:00',
                    'break_end' => '19:30',
                ]
            ],
        ]);

        $response->assertSessionHasErrors(['break_error' => '休憩時間が勤務時間外です']);

        $response = $this->post(route('user.edit', ['attendance_id' => $attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['description' => '備考を記入してください']);
    }
    /** 修正申請処理まとめ */
    public function test_edit_attendance()
    {
        $user = User::factory()->create([
            'name' => 'testUser',
            'role' => 'user',
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $this->actingAs($user);
        $attendance1 = Attendance::where('user_id', $user->id)->first();
        $attendance1->update([
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->post(route('user.edit', ['attendance_id' => $attendance1->id]), [
            'year' => now()->format('Y年'),
            'month_day' => now()->format('n月j日'),
            'clock_in' => '09:00',
            'clock_out' => '19:00',
            'description' => '修正依頼テスト',
        ]);
        $response = $this->post(route('user.edit', ['attendance_id' => $attendance2->id]), [
            'year' => now()->format('Y年'),
            'month_day' => now()->format('n月j日'),
            'clock_in' => '09:00',
            'clock_out' => '19:00',
            'description' => '修正依頼テスト2',
        ]);
        /* 自分の申請一覧 */
        $response = $this->get(route('user.request'));
        $response->assertSee('修正依頼テスト');
        $response->assertSee('修正依頼テスト2');
        /* 管理者の申請一覧画面と承認画面 */
        $admin = User::first();
        $this->actingAs($admin, 'admin');
        $user = User::where('name', 'testUser')->first();
        $attendance1 = Attendance::where('user_id', $user->id)->first();
        $userRequest1 = UserRequest::where('attendance_id', $attendance1->id)->first();

        $response = $this->get(route('user.request'));
        $response->assertSee($userRequest1->user->name);

        $response = $this->get(route('admin.showRequest', ['attendance_correct_request' => $userRequest1->id]));
        $response->assertSee($user->name);
        $response->assertSee(now()->format('Y年'));
        $response->assertSee(now()->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('19:00');
        /* 承認済み表示 */
        $response = $this->post(route('admin.approve', ['attendance_correct_request' => $userRequest1->id]));

        $attendance2 = Attendance::where('user_id', $user->id)->firstWhere('id', 2);
        $userRequest2 = UserRequest::where('attendance_id', $attendance2->id)->first();
        $response = $this->post(route('admin.approve', ['attendance_correct_request' => $userRequest2->id]));

        $this->actingAs($user);
        $response = $this->get(route('user.request', array_merge(request()->query(), ['tab' => 'approved'])));
        $response->assertSee('修正依頼テスト');
        $response->assertSee('修正依頼テスト2');
        /* 詳細 */
        $response = $this->get(route('user.request'));
        $response->assertSee('詳細');

        $response = $this->get(route('user.show', ['attendance_id' => $attendance1->id]));
        $response->assertStatus(200);
        $response = $this->get(route('user.show', ['attendance_id' => $attendance2->id]));
        $response->assertStatus(200);
    }
}
