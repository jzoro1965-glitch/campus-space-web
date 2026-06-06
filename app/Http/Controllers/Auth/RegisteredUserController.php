<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Tampilkan form registrasi.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Proses pembuatan akun baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nim'      => ['required', 'string', 'max:20', 'unique:users'],
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'nim'      => $request->nim,
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'mahasiswa', // default role untuk user baru
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('mahasiswa.home'));
    }
}
