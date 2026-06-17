<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class AuthApiController extends Controller
{
    /** POST /api/login */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json(['success' => false, 'message' => 'Email atau password salah.'], 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    /** POST /api/register */
    public function register(Request $request)
    {
        $request->validate([
            'nim'      => ['required', 'string', 'max:20', 'unique:users'],
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::min(6)],
        ]);

        $user = User::create([
            'nim'      => $request->input('nim'),
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role'     => 'mahasiswa',
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil.',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ], 201);
    }

    /** POST /api/logout */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil.']);
    }

    /** GET /api/profile */
    public function profile(Request $request)
    {
        return response()->json(['success' => true, 'data' => $this->formatUser($request->user())]);
    }

    /** PUT /api/profile */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'  => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'nim'   => ['sometimes', 'string', 'max:20', 'unique:users,nim,' . $user->id],
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => ['confirmed', Rules\Password::min(6)]]);
            $user->password = Hash::make($request->input('password'));
        }

        $user->fill($request->only('name', 'email', 'nim'));
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $this->formatUser($user),
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'             => $user->id,
            'nim'            => $user->nim,
            'name'           => $user->name,
            'email'          => $user->email,
            'role'           => $user->role,
            'is_super_admin' => (bool) $user->is_super_admin,
        ];
    }
}
