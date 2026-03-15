<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\DTOs\LoginDTO;
use App\Modules\User\DTOs\RegisterDTO;
use App\Modules\User\Requests\LoginRequest;
use App\Modules\User\Requests\RegisterRequest;
use App\Modules\User\Requests\SendOtpRequest;
use App\Modules\User\Requests\VerifyOtpRequest;
use App\Modules\User\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Socialite\Facades\Socialite;

class WebAuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function showLogin(Request $request): Response
    {
        $redirect = $this->sanitizeRedirect($request->query('redirect'));

        if ($redirect) {
            $request->session()->put('auth.redirect_to', $redirect);
        }

        return Inertia::render('Auth/Login', [
            'googleClientId' => config('services.google.client_id'),
            'redirectTo' => $redirect,
        ]);
    }

    public function login(LoginRequest $request)
    {
        $dto = new LoginDTO(...$request->validated());
        $result = $this->authService->login($dto);

        Auth::login($result['user'], $dto->remember);

        $request->session()->regenerate();

        $redirectTo = $this->resolveRedirectTo($request);
        if ($redirectTo) {
            return redirect()->to($redirectTo);
        }

        return redirect()->to($this->resolveDashboardPath($result['user']));
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(RegisterRequest $request)
    {
        $dto = new RegisterDTO(...$request->validated());
        $user = $this->authService->register($dto);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->to($this->resolveDashboardPath($user))
            ->with('success', 'Registration successful. Please verify your email.');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function showForgotPassword(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        return back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request, string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill(['password' => bcrypt($password)])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect('/login')->with('status', __($status));
        }

        return back()->withErrors(['email' => [__($status)]]);
    }

    public function redirectToGoogle()
    {
        $redirectTo = $this->sanitizeRedirect(request()->query('redirect'));
        if ($redirectTo) {
            session()->put('auth.redirect_to', $redirectTo);
        }

        return Inertia::location(
            Socialite::driver('google')->redirect()->getTargetUrl()
        );
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();
            $result = $this->authService->handleGoogleCallback([
                'id'     => $googleUser->getId(),
                'email'  => $googleUser->getEmail(),
                'name'   => $googleUser->getName(),
                'avatar' => $googleUser->getAvatar(),
            ]);
            Auth::login($result['user'], true);
            $request->session()->regenerate();

            $redirectTo = $this->resolveRedirectTo($request);
            if ($redirectTo) {
                return redirect()->to($redirectTo);
            }

            return redirect()->to($this->resolveDashboardPath($result['user']));
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }
    }

    public function handleGoogleOneTap(Request $request)
    {
        $request->validate([
            'credential' => ['required', 'string'],
            'redirect' => ['nullable', 'string'],
        ]);

        $redirectTo = $this->sanitizeRedirect($request->input('redirect'));
        if ($redirectTo) {
            $request->session()->put('auth.redirect_to', $redirectTo);
        }

        $response = Http::timeout(8)->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->string('credential')->toString(),
        ]);

        if (! $response->ok()) {
            return response()->json(['message' => 'Google token verification failed.'], 422);
        }

        $tokenData = $response->json();
        $clientId = config('services.google.client_id');

        if (($tokenData['aud'] ?? null) !== $clientId) {
            return response()->json(['message' => 'Invalid Google token audience.'], 422);
        }

        if (($tokenData['email_verified'] ?? 'false') !== 'true') {
            return response()->json(['message' => 'Google email is not verified.'], 422);
        }

        if (empty($tokenData['sub']) || empty($tokenData['email'])) {
            return response()->json(['message' => 'Google token payload is incomplete.'], 422);
        }

        $result = $this->authService->handleGoogleCallback([
            'id' => $tokenData['sub'] ?? null,
            'email' => $tokenData['email'] ?? null,
            'name' => $tokenData['name'] ?? ($tokenData['email'] ?? 'Google User'),
            'avatar' => $tokenData['picture'] ?? null,
        ]);

        Auth::login($result['user'], true);
        $request->session()->regenerate();

        return response()->json([
            'redirect' => $this->resolveRedirectTo($request) ?? $this->resolveDashboardPath($result['user']),
        ]);
    }

    public function showOtpLogin(): Response
    {
        return Inertia::render('Auth/OtpLogin', [
            'redirectTo' => $this->sanitizeRedirect(request()->query('redirect')),
        ]);
    }

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $this->authService->sendOtp($request->validated('phone'));

        return response()->json([
            'message' => 'OTP sent successfully.',
        ]);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $result = $this->authService->verifyOtp(
            $request->validated('phone'),
            $request->validated('otp')
        );

        Auth::login($result['user'], true);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'OTP verified successfully.',
            'redirect' => $this->resolveRedirectTo($request) ?? $this->resolveDashboardPath($result['user']),
            'user' => [
                'id' => $result['user']->id,
                'name' => $result['user']->name,
                'roles' => $result['user']->getRoleNames()->values(),
            ],
        ]);
    }

    public function showVerifyEmail(): Response
    {
        return Inertia::render('Auth/VerifyEmail');
    }

    private function resolveRedirectTo(Request $request): ?string
    {
        $fromRequest = $this->sanitizeRedirect($request->input('redirect'));
        if ($fromRequest) {
            return $fromRequest;
        }

        $fromSession = $this->sanitizeRedirect($request->session()->pull('auth.redirect_to'));
        if ($fromSession) {
            return $fromSession;
        }

        return null;
    }

    private function sanitizeRedirect(?string $redirect): ?string
    {
        if (! $redirect) {
            return null;
        }

        $redirect = trim($redirect);
        if ($redirect === '' || ! str_starts_with($redirect, '/') || str_starts_with($redirect, '//')) {
            return null;
        }

        return $redirect;
    }

    private function resolveDashboardPath($user): string
    {
        return method_exists($user, 'dashboardPath') ? $user->dashboardPath() : '/';
    }
}
