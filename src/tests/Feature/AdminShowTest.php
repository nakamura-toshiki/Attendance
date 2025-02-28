<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AdminShowTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }
    /** 勤怠詳細情報の一致 */
    public function test_display_attendance_details()
    {
        $admin = User::first();
        $this->actingAs($admin, 'admin');
        
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);
        
        $response = $this->get(route('user.show', ['attendance_id' => $attendance->id]));
        $response->assertStatus(200);

        $response->assertSee($user->name);
        $response->assertSee(now()->format('Y年'));
        $response->assertSee(now()->format('n月j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
    /**バリデーション */
    public function test_invalid_fields_in_attendance_form()
    {
        $admin = User::first();
        $this->actingAs($admin, 'admin');
        
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->format('Y-m-d'),
            'clock_in' => '09:00',
            'clock_out' => '18:00',
        ]);

        $response = $this->get(route('user.show', ['attendance_id' => $attendance->id]));
        $response->assertStatus(200);

        $response = $this->post(route('admin.edit', ['attendance_id' => $attendance->id]), [
            'clock_in' => '19:00',
            'clock_out' => '18:00',
        ]);

        $response->assertSessionHasErrors(['clock_out' => '出勤時間もしくは退勤時間が不適切な値です']);
        $response->assertRedirect();

        $response = $this->post(route('admin.edit', ['attendance_id' => $attendance->id]), [
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

        $response = $this->post(route('admin.edit', ['attendance_id' => $attendance->id]), [
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

        $response = $this->post(route('admin.edit', ['attendance_id' => $attendance->id]), [
            'clock_in' => '09:00',
            'clock_out' => '18:00',
            'description' => '',
        ]);

        $response->assertSessionHasErrors(['description' => '備考を記入してください']);
    }
}
