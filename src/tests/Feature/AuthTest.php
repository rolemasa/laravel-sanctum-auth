<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_ユーザー登録できる(): void
    {
        $res = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $res->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_ログインできる(): void
    {
        $user = User::factory()->create();   // パスワードは 'password'

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_誤ったパスワードではログインできない(): void
    {
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_未ログインではダッシュボードに入れない(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_ログアウトできる(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_トークンでAPIにアクセスできる(): void
    {
        $user = User::factory()->create();

        $token = $this->postJson('/api/tokens', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()->json('token');

        $this->withToken($token)->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', $user->email);
    }

    public function test_未認証ではAPIが401になる(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
    }
}