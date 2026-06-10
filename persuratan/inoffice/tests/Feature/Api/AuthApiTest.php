<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat role default untuk test
        Role::firstOrCreate(
            ['slug' => 'operator'],
            ['nama_role' => 'Operator', 'permissions' => ['*']]
        );
    }

    /** @test */
    public function test_login_berhasil_dengan_username_dan_password_yang_benar(): void
    {
        $user = User::factory()->create([
            'username'  => 'testuser',
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'token',
                     'user' => ['id', 'username', 'nama_lengkap', 'email'],
                 ]);

        $this->assertNotEmpty($response->json('token'));
    }

    /** @test */
    public function test_login_dengan_email_juga_berhasil(): void
    {
        $user = User::factory()->create([
            'email'     => 'test@example.com',
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('user.email', 'test@example.com');
    }

    /** @test */
    public function test_login_gagal_dengan_password_salah(): void
    {
        User::factory()->create([
            'username'  => 'testuser',
            'password'  => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'testuser',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJsonPath('message', 'Username/email atau password salah.');
    }

    /** @test */
    public function test_login_gagal_untuk_akun_tidak_aktif(): void
    {
        User::factory()->create([
            'username'  => 'inactiveuser',
            'password'  => bcrypt('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'login'    => 'inactiveuser',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function test_endpoint_me_mengembalikan_data_user_yang_login(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
                 ->assertJsonPath('data.id', $user->id)
                 ->assertJsonPath('data.username', $user->username);
    }

    /** @test */
    public function test_endpoint_me_menolak_request_tanpa_token(): void
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }

    /** @test */
    public function test_logout_berhasil_menghapus_token(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Logout berhasil.');

        // Pastikan token sudah dihapus
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    /** @test */
    public function test_login_validasi_field_kosong(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['login', 'password']);
    }
}
