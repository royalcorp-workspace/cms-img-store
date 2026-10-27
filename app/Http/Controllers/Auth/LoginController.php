<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::guard('admin')->check()) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $loginInput = strtolower(trim((string) $request->input('email')));
        $request->merge([
            'email' => $loginInput,
        ]);

        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ], [
            'email.required' => 'Email atau username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $admin = Admin::where('deleted', false)
            ->where(function ($query) use ($loginInput) {
                $query->whereRaw('LOWER(email) = ?', [$loginInput])
                      ->orWhereRaw('LOWER(name) = ?', [$loginInput])
                      ->orWhereHas('user', function ($q) use ($loginInput) {
                          $q->whereRaw('LOWER(username) = ?', [$loginInput])
                            ->orWhereRaw('LOWER(email) = ?', [$loginInput]);
                      });
            })
            ->first();

        $credentials = [
            'email' => $admin ? $admin->email : $loginInput,
            'password' => $request->input('password'),
        ];

        if (Auth::guard('admin')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/dashboard');
        }

        throw ValidationException::withMessages([
            'email' => 'Email/username atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
