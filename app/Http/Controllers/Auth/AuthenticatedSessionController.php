<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider; // 👈 IMPORTANTE: Agregar este use
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // AQUÍ VA LA REDIRECCIÓN POR ROL
        return redirect()->intended($this->redirectToByRole());
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function redirectToByRole(): string
    {
        $user = Auth::user();

        if (!$user) {
            return '/';
        }

        return match($user->tipo) {
            'admin' => RouteServiceProvider::ADMIN_DASHBOARD,
            'fundacion' => RouteServiceProvider::FUNDACION_DASHBOARD,
            'veterinaria' => RouteServiceProvider::VETERINARIA_DASHBOARD,
            default => RouteServiceProvider::HOME,
        };
    }
}
