<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\UserRequest;
use App\Models\UserRequestBreak;

class AdminApproveTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }
    /** 勤怠情報修正まとめ */
    public function test_display_approve_views()
    {
        $user = User::factory()->create([
            'name' => 'testUser',
            'role' => 'user',
        ]);
        $attendance1 = Attendance::where('user_id', $user->id)->first();
        $attendance1->update([
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);
        $userRequest1 = UserRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance1->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '20:00',
            'description' => '修正依頼テスト',
        ]);
        $userRequestBreak = UserRequestBreak::create([
            'user_request_id' => $userRequest1->id,
            'break_start' => '12:00',
            'break_end' => '13:00',
        ]);
        $userRequest2 = UserRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance2->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '17:00',
            'description' => '修正依頼テスト2',
        ]);

        $admin = User::first();
        $this->actingAs($admin, 'admin');
        /* 承認待ちの表示 */
        $response = $this->get(route('user.request'));
        $response->assertSee('修正依頼テスト');
        $response->assertSee('修正依頼テスト2');
        /* 申請内容の表示 */
        $response = $this->get(route('admin.showRequest', ['attendance_correct_request' => $userRequest1->id]));
        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee(now()->format('Y年'));
        $response->assertSee(now()->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('20:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');

        $response = $this->post(route('admin.approve', ['attendance_correct_request' => $userRequest1->id]));
        /* 勤怠情報の更新を確認 */
        $this->assertDatabaseHas('attendances', [
            'id' => $attendance1->id,
            'clock_out' => '20:00:00',
        ]);

        $response = $this->post(route('admin.approve', ['attendance_correct_request' => $userRequest2->id]));
        /* 承認済みの表示 */
        $response = $this->get(route('user.request', array_merge(request()->query(), ['tab' => 'approved'])));
        $response->assertSee('修正依頼テスト');
        $response->assertSee('修正依頼テスト2');
    }
}
