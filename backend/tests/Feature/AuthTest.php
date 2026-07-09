<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/register', [
            'name' => 'Yeabsira',
            'email' => 'user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Registration successful. Please verify your email.')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email', 'avatar', 'email_verified_at', 'created_at'],
                    'token',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'name' => 'Yeabsira',
        ]);
    }

    public function test_validation_errors_work(): void
    {
        $response = $this->postJson('/api/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_verification_email_sent_on_registration(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $user->sendEmailVerificationNotification();

        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonPath('data.user.email', $user->email);
    }

    public function test_unverified_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('status', false);
    }

    public function test_google_login_works(): void
    {
        $googleUser = new class
        {
            public function getEmail(): string
            {
                return 'google@example.com';
            }

            public function getId(): string
            {
                return 'google-123';
            }

            public function getName(): string
            {
                return 'Google User';
            }

            public function getNickname(): string
            {
                return 'Google User';
            }

            public function getAvatar(): string
            {
                return 'https://example.com/avatar.png';
            }
        };

        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->andReturnSelf();
        $driver->shouldReceive('userFromToken')->with('google_access_token')->andReturn($googleUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);

        $response = $this->postJson('/api/google/login', [
            'token' => 'google_access_token',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.user.email', 'google@example.com');

        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'google_id' => 'google-123',
            'provider' => 'google',
        ]);
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logout successful');
    }

    public function test_password_reset_works(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/forgot-password', [
            'email' => $user->email,
        ])->assertOk();

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->putJson('/api/profile', [
            'name' => 'Updated Name',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');
    }

    public function test_user_can_delete_account(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('message', 'Account deleted successfully');

        $this->assertSoftDeleted('users', [
            'email' => $user->email,
        ]);
    }
}