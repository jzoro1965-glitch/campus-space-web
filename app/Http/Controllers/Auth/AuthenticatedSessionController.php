<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthenticatedSessionController extends Controller
{
    /**
     * Tampilkan form login.
     * Kalau user sudah punya session aktif → redirect ke dashboard.
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Proses login.
     *
     * Solusi masalah "tidak bisa login setelah close browser":
     * 1. Cari user berdasarkan email SEBELUM attempt
     * 2. Kalau user ditemukan → hapus SEMUA session lama user itu dari DB
     * 3. Baru jalankan Auth::attempt()
     * 4. Ini memastikan session lama (yang browsernya sudah ditutup) tidak menghalangi login baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Step 1: Cari user dulu berdasarkan email
        $user = \App\Models\User::where('email', $request->email)->first();

        // Step 2: Kalau user ada, hapus SEMUA session lama di DB sebelum login
        // Ini yang jadi root cause masalah: session lama masih hidup di DB
        // walau browser sudah ditutup
        if ($user) {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->delete();
        }

        // Step 3: Jalankan autentikasi
        if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();

            $loggedUser = Auth::user();

            if ($loggedUser->role === 'admin') {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('mahasiswa.home');
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak cocok.',
        ])->onlyInput('email');
    }

    /**
     * Logout — hapus session dan redirect ke login.
     */
    public function destroy(Request $request)
    {
        // Hapus record session dari DB secara eksplisit
        DB::table('sessions')
            ->where('id', $request->session()->getId())
            ->delete();

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
