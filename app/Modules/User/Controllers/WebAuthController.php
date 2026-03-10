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
use Inertia\Inertia;
use Inertia\Response;

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

        Auth::login($result['user']);

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
}
