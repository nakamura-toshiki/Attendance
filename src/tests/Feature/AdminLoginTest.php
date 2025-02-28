<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;
use App\Models\User;

class AdminLoginTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /**管理者ログイン機能 */
    /**メールアドレスが未入力 */
    public function test_login_requires_email()
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => '',
            'password' => 'admin12345',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    /**パスワードが未入力 */
    public function test_login_requires_password()
    {
        $response = $this->from('/admin/login')->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
        $response->assertStatus(302);
        $response->assertRedirect('/admin/login');
    }

    /**入力情報が間違っている */
    public function test_login_invalid_credentials_error()
    {
        $invalidCredentials = [
            'email' => 'nonexistent@example.com',
            'password' => 'invalid-password',
        ];

        $response = $this->from('/admin/login')->post('/admin/login', $invalidCredentials);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['login_error' => 'ログイン情報が登録されていません']);
        $response->assertRedirect('/admin/login');
    }

    /**全ての項目が正しく入力されている場合 */
    public function test_successful_login()
    {
        $user = User::first();

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'admin12345',
        ]);

        $this->assertAuthenticatedAs($user, 'admin');
        $response->assertRedirect(route('admin.list'));
    }
}
