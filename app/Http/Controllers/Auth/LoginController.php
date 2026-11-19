<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'is_active' => true])) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak sesuai, atau akun tidak aktif.',
            ]);
        }

        if (Auth::user()->roles->isEmpty()) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => 'Akun belum memiliki peran. Hubungi administrator.',
            ]);
        }

        $request->session()->regenerate();

        $destination = Auth::user()->can('dashboard.view')
            ? route('dashboard')
            : (Auth::user()->can('master.type_examinations.view')
                ? route('master.type_examinations.index')
                : (Auth::user()->can('master.users.view')
                    ? route('master.users.index')
                    : (Auth::user()->can('access.roles.view')
                        ? route('access.roles.index')
                        : route('dashboard'))));

        return redirect()->intended($destination);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
