<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Daftar semua user dengan pencarian & filter role
     */
    public function index(Request $request)
    {
        $query = User::withCount('bookings')
            ->orderByDesc('is_super_admin')
            ->orderBy('role')
            ->orderBy('name');

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name',  'like', "%{$s}%")
                  ->orWhere('nim',   'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->paginate(20)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Form tambah user baru
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Simpan user baru yang dibuat admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'nim'      => ['required', 'string', 'max:20', 'unique:users'],
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users'],
            'role'     => ['required', 'in:admin,mahasiswa'],
            'password' => ['required', 'confirmed', Rules\Password::min(6)],
        ]);

        User::create([
            'nim'      => $request->input('nim'),
            'name'     => $request->input('name'),
            'email'    => $request->input('email'),
            'role'     => $request->input('role'),
            'password' => Hash::make($request->input('password')),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$request->input('name')} berhasil ditambahkan.");
    }

    /**
     * Form edit data user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update data user (tanpa ganti role — role punya endpoint sendiri)
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'nim'  => ['required', 'string', 'max:20', 'unique:users,nim,' . $user->id],
            'name' => ['required', 'string', 'max:255'],
            'email'=> ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
        ]);

        $user->update([
            'nim'   => $request->input('nim'),
            'name'  => $request->input('name'),
            'email' => $request->input('email'),
        ]);

        // Ganti password hanya kalau diisi
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::min(6)],
            ]);
            $user->update(['password' => Hash::make($request->input('password'))]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "Data {$user->name} berhasil diperbarui.");
    }

    /**
     * Hapus user — super admin tidak bisa dihapus & tidak bisa hapus diri sendiri
     */
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa menghapus akun Anda sendiri.');
        }

        if ($user->is_super_admin) {
            return back()->with('error', 'Super Admin tidak bisa dihapus.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Akun {$name} berhasil dihapus.");
    }

    /**
     * Ubah role user — aturan: hanya super admin yang bisa demote
     */
    public function updateRole(Request $request, User $user)
    {
        $actor = Auth::user();

        if ($user->id === $actor->id) {
            return back()->with('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');
        }

        if ($user->is_super_admin) {
            return back()->with('error', 'Role Super Admin tidak bisa diubah.');
        }

        $request->validate([
            'role' => ['required', 'in:admin,mahasiswa'],
        ]);

        $roleTarget = $request->input('role');

        if (! $actor->is_super_admin && $roleTarget === 'mahasiswa') {
            return back()->with('error', 'Hanya Super Admin yang bisa menurunkan role ke Mahasiswa.');
        }

        if ($user->role === $roleTarget) {
            return back()->with('error', 'Role tidak berubah karena sudah sama.');
        }

        $user->update(['role' => $roleTarget]);

        $pesan = $roleTarget === 'admin'
            ? "{$user->name} berhasil dipromosikan menjadi Admin."
            : "{$user->name} berhasil dikembalikan menjadi Mahasiswa.";

        return back()->with('success', $pesan);
    }
}
