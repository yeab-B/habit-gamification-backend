<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\NewAccessToken;
use Laravel\Socialite\Facades\Socialite;
use RuntimeException;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $user->sendEmailVerificationNotification();

        return [
            'user' => $user->fresh(),
            'token' => $this->createToken($user),
        ];
    }

    public function login(array $data): array
    {
        $user = User::query()->where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw new RuntimeException('Invalid credentials.');
        }

        if (! $user->hasVerifiedEmail()) {
            throw new RuntimeException('Email address is not verified.');
        }

        return [
            'user' => $user,
            'token' => $this->createToken($user),
        ];
    }

    public function googleLogin(string $token): array
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->userFromToken($token);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Failed to authenticate with Google: ' . $e->getMessage(), 0, $e);
        }
         $user = User::withTrashed()->where('email', $googleUser->getEmail())->first();

        if (! $user) {
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: 'Google User',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'provider' => 'google',
                'email_verified_at' => now(),
            ]);
        } else {
            if ($user->trashed()) {
                $user->restore();
            }

            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'provider' => 'google',
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        }
        return [
            'user' => $user->fresh(),
            'token' => $this->createToken($user),
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function sendPasswordResetLink(string $email): void
    {
        Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): void
    {
        $status = Password::reset(
            [
                'email' => $data['email'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
                'token' => $data['token'],
            ],
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new RuntimeException(__($status));
        }
    }

    public function updateProfile(User $user, array $data): User
    {
        $avatarPath = $user->avatar;

        if (array_key_exists('avatar', $data)) {
            if ($data['avatar'] instanceof UploadedFile) {
                if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $avatarPath = $data['avatar']->store('avatars', 'public');
            } elseif (is_null($data['avatar'])) {
                if ($user->avatar && !str_starts_with($user->avatar, 'http')) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $avatarPath = null;
            }
        }

        $user->forceFill([
            'name' => $data['name'],
            'avatar' => $avatarPath,
        ])->save();

        return $user->fresh();
    }

    public function changePassword(User $user, array $data): void
    {
        $user->forceFill([
            'password' => $data['password'],
        ])->save();

        $user->tokens()->delete();
    }

    public function deleteAccount(User $user): void
    {
        $user->tokens()->delete();
        $user->delete();
    }

    private function createToken(User $user): string
    {
        /** @var NewAccessToken $token */
        $token = $user->createToken('mobile');

        return $token->plainTextToken;
    }
}