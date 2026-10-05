<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'NIM, Username, atau Email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $loginInput = $credentials['login'];
        $password = $credentials['password'];
        $remember = $request->boolean('remember');

        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->orWhereHas('student', function ($query) use ($loginInput) {
                $query->where('nim', $loginInput);
            })
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            if (! $user->is_active) {
                return back()->withErrors([
                    'login' => 'Akun Anda dinonaktifkan. Silakan hubungi administrator.',
                ]);
            }

            Auth::login($user, $remember);
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user)
                ->with('success', 'Selamat datang kembali, '.$user->name.'!');
        }

        return back()->withErrors([
            'login' => 'Kredensial yang diberikan tidak cocok dengan data kami.',
        ])->onlyInput('login');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'Anda telah berhasil keluar.');
    }

    protected function redirectBasedOnRole($user): RedirectResponse
    {
        return match ($user->role) {
            UserRole::MAHASISWA => redirect()->route('student.dashboard'),
            UserRole::WR2 => redirect()->route('wr2.dashboard'),
            UserRole::SARPRAS => redirect()->route('sarpras.dashboard'),
            default => redirect()->route('login'),
        };
    }
}
