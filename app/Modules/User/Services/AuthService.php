<?php

namespace App\Modules\User\Services;

use App\Models\Otp;
use App\Models\User;
use App\Services\Sms\InfobipSmsService;
use App\Modules\User\DTOs\LoginDTO;
use App\Modules\User\DTOs\RegisterDTO;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly InfobipSmsService $smsService
    ) {}

    public function register(RegisterDTO $dto): User
    {
        $user = User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
            'phone' => $dto->phone,
        ]);

        $user->assignRole('user');
        $user->sendEmailVerificationNotification();

        return $user;
    }

    public function login(LoginDTO $dto): array
    {
        $user = User::where('email', $dto->email)->first();

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->isBanned()) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been banned.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function sendOtp(string $phone): void
    {
        Otp::where('identifier', $phone)
            ->where('type', 'phone')
            ->whereNull('verified_at')
            ->delete();

        $otpLength = (int) config('snackzar.otp.length', 6);
        $maxValue = (10 ** $otpLength) - 1;
        $otpCode = str_pad((string) random_int(0, $maxValue), $otpLength, '0', STR_PAD_LEFT);

        $otpRecord = Otp::create([
            'identifier' => $phone,
            'otp' => Hash::make($otpCode),
            'type' => 'phone',
            'expires_at' => now()->addMinutes(config('snackzar.otp.expiry_minutes', 10)),
        ]);

        try {
            $this->smsService->sendOtp($phone, $otpCode);
        } catch (Throwable $exception) {
            $otpRecord->delete();

            throw ValidationException::withMessages([
                'phone' => ['Unable to send OTP right now. Please try again shortly.'],
            ]);
        }
    }

    public function verifyOtp(string $phone, string $otp): array
    {
        $otpRecord = Otp::where('identifier', $phone)
            ->where('type', 'phone')
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (! $otpRecord) {
            throw ValidationException::withMessages([
                'otp' => ['No OTP found. Please request a new one.'],
            ]);
        }

        if ($otpRecord->isExpired()) {
            throw ValidationException::withMessages([
                'otp' => ['OTP has expired. Please request a new one.'],
            ]);
        }

        if (! Hash::check($otp, $otpRecord->otp)) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP.'],
            ]);
        }

        $otpRecord->update(['verified_at' => now()]);

        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'User',
                'phone_verified_at' => now(),
                'status' => 'active',
            ]
        );

        if (! $user->phone_verified_at) {
            $user->update(['phone_verified_at' => now()]);
        }

        if ($user->getRoleNames()->isEmpty()) {
            $user->assignRole('user');
        }

        $user->loadMissing('roles');

        $token = $user->createToken('otp-auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function handleGoogleCallback(array $googleUser): array
    {
        $user = User::where('google_id', $googleUser['id'])
            ->orWhere('email', $googleUser['email'])
            ->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser['id'],
                'avatar' => $googleUser['avatar'] ?? $user->avatar,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
        } else {
            $user = User::create([
                'name' => $googleUser['name'],
                'email' => $googleUser['email'],
                'google_id' => $googleUser['id'],
                'avatar' => $googleUser['avatar'] ?? null,
                'email_verified_at' => now(),
                'status' => 'active',
            ]);
            $user->assignRole('user');
        }

        $user->loadMissing('roles');

        $token = $user->createToken('google-auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function forgotPassword(string $email): string
    {
        $status = Password::sendResetLink(['email' => $email]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }

    public function resetPassword(array $data): string
    {
        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        return $status;
    }
}
