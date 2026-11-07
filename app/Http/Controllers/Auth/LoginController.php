<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const LOCK_SECONDS = 60;

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $key = 'login:'.$request->input('email').'|'.($request->ip() ?? 'anon');

        // Si ya superó el límite, bloquear y mostrar tiempo restante
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            return back()
                ->withInput($request->only('email'))
                ->with('login_locked', $seconds);
        }

        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            RateLimiter::clear($key);

            // OWASP ASVS 2.4.3/2.4.4: rehash bajo demanda si aumenta el coste
            if (Hash::needsRehash(auth()->user()->password)) {
                auth()->user()->update(['password' => Hash::make($request->password)]);
            }

            return redirect()->intended(route('dashboard'))->with('success', 'Sesión iniciada con éxito.');
        }

        // Credenciales incorrectas → sumar intento y calcular restantes
        RateLimiter::hit($key, self::LOCK_SECONDS);
        $remaining = self::MAX_ATTEMPTS - RateLimiter::attempts($key);

        return back()
            ->withInput($request->only('email'))
            ->with('remaining_attempts', $remaining);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Sesión cerrada con éxito.');
    }
}
