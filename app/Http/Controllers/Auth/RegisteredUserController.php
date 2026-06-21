<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        // Kalau sudah login, redirect ke dashboard sesuai role
        if (Auth::check()) {
            return Auth::user()->role === 'admin'
                ? redirect()->route('admin.dashboard')
                : redirect()->route('mahasiswa.home');
        }

        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nim'      => ['required', 'string', 'max:20', 'unique:users,nim'],
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::min(6)],
        ]);

        $user = User::create([
            'nim'      => $request->nim,
            'name'     => $request->name,
            'email'    => strtolower($request->email),
            'password' => Hash::make($request->password),
            'role'     => 'mahasiswa',
        ]);

        event(new Registered($user));

        // Bersihkan session lama sebelum login
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('mahasiswa.home');
    }
}
