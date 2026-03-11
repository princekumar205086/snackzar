<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\User\DTOs\LoginDTO;
use App\Modules\User\DTOs\RegisterDTO;
use App\Modules\User\Requests\LoginRequest;
use App\Modules\User\Requests\RegisterRequest;
use App\Modules\User\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(LoginRequest $request)
    {
        $dto = new LoginDTO(...$request->validated());
        $result = $this->authService->login($dto);

        Auth::login($result['user'], $dto->remember);

        $request->session()->regenerate();

        $user = $result['user'];
        if ($user->hasRole('admin')) {
            return redirect()->intended('/admin');
        } elseif ($user->hasRole('seller')) {
            return redirect()->intended('/seller');
        } elseif ($user->hasRole('delivery_partner')) {
            return redirect()->intended('/delivery');
        }

        return redirect()->intended('/');
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

        return redirect('/')->with('success', 'Registration successful. Please verify your email.');
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

            $user = $result['user'];
            if ($user->hasRole('admin')) return redirect('/admin/dashboard');
            if ($user->hasRole('seller')) return redirect('/seller/dashboard');
            if ($user->hasRole('delivery_partner')) return redirect('/delivery/dashboard');
            return redirect('/dashboard');
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Google authentication failed. Please try again.']);
        }
    }

    public function showOtpLogin(): Response
    {
        return Inertia::render('Auth/OtpLogin');
    }

    public function showVerifyEmail(): Response
    {
        return Inertia::render('Auth/VerifyEmail');
    }
}
