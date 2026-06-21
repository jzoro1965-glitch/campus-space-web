<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan form login.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Proses autentikasi user.
     * - Invalidate semua session lama user yang sama sebelum login
     * - Regenerate session ID baru setiap login
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Hapus semua session lama milik user ini dari DB
            // Ini fix masalah: browser ditutup tanpa logout → session lama masih ada
            $user = Auth::user();
            \DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', $request->session()->getId())
                ->delete();

            $request->session()->regenerate();

            if ($user->role === 'admin') {
                return redirect(route('admin.dashboard'));
            }

            return redirect(route('mahasiswa.home'));
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak cocok.',
        ])->onlyInput('email');
    }

    /**
     * Proses logout user.
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
