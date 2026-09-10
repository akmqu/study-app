<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        $home = $user->role === 'tutor'
            ? route('tutor.dashboard', absolute: false)
            : route('student.dashboard', absolute: false);

        $intended = $request->session()->pull('url.intended');

        if (is_string($intended) && $this->intendedUrlIsAllowedForRole($intended, $user->role)) {
            return redirect()->to($intended);
        }

        return redirect()->to($home);
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

    private function intendedUrlIsAllowedForRole(string $url, string $role): bool
    {
        $path = parse_url($url, PHP_URL_PATH) ?? '';

        if ($path === '/dashboard') {
            return true;
        }

        return match ($role) {
            'tutor' => str_starts_with($path, '/tutor'),
            'student' => str_starts_with($path, '/student'),
            default => false,
        };
    }
}
