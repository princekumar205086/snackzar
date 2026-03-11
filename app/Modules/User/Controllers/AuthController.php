<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Traits\ApiResponse;
use App\Modules\User\DTOs\LoginDTO;
use App\Modules\User\DTOs\RegisterDTO;
use App\Modules\User\Requests\ForgotPasswordRequest;
use App\Modules\User\Requests\LoginRequest;
use App\Modules\User\Requests\RegisterRequest;
use App\Modules\User\Requests\ResetPasswordRequest;
use App\Modules\User\Requests\SendOtpRequest;
use App\Modules\User\Requests\VerifyOtpRequest;
use App\Modules\User\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Authentication
 *
 * APIs for user registration, login, logout, OTP, password reset, and email verification.
 */
class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register
     *
     * Create a new user account and receive an auth token.
     *
     * @unauthenticated
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = new RegisterDTO(...$request->validated());
        $user = $this->authService->register($dto);
        $token = $user->createToken('auth-token')->plainTextToken;

        return $this->created([
            'user' => $user,
            'token' => $token,
        ], 'Registration successful. Please verify your email.');
    }

    /**
     * Login
     *
     * Authenticate a user and receive a Bearer token.
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $dto = new LoginDTO(...$request->validated());
        $result = $this->authService->login($dto);

        return $this->success($result, 'Login successful.');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully.');
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load('roles', 'permissions');

        return $this->success($user);
    }

    /**
     * Send OTP
     *
     * Send a one-time password to the given phone number.
     *
     * @unauthenticated
     */
    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $this->authService->sendOtp($request->validated('phone'));

        return $this->success(null, 'OTP sent successfully.');
    }

    /**
     * Verify OTP
     *
     * Verify the OTP sent to the phone number.
     *
     * @unauthenticated
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyOtp(
            $request->validated('phone'),
            $request->validated('otp')
        );

        return $this->success($result, 'OTP verified successfully.');
    }

    /**
     * Forgot Password
     *
     * Send a password reset link to the given email.
     *
     * @unauthenticated
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->authService->forgotPassword($request->validated('email'));

        return $this->success(null, 'Password reset link sent.');
    }

    /**
     * Reset Password
     *
     * Reset the user's password using the token from the reset email.
     *
     * @unauthenticated
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->validated());

        return $this->success(null, 'Password reset successful.');
    }

    public function verifyEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->success(null, 'Email already verified.');
        }

        $request->user()->markEmailAsVerified();

        return $this->success(null, 'Email verified successfully.');
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->success(null, 'Email already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return $this->success(null, 'Verification email sent.');
    }
}
