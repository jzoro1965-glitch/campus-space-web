<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Kelola User</h2>
                <p class="text-xs text-gray-400 mt-0.5">Tambah, edit, hapus akun & atur role pengguna</p>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah User
            </a>
        </div>
    </x-slot>

    {{-- Filter & Search --}}
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, NIM, atau email..."
               class="px-4 py-2 border border-gray-300 rounded-xl text-sm w-72 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="role" class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Role</option>
            <option value="admin"     {{ request('role') === 'admin'     ? 'selected' : '' }}>Admin</option>
            <option value="mahasiswa" {{ request('role') === 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">Cari</button>
        @if(request()->hasAny(['search','role']))
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-colors">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 text-left">#</th>
                    <th class="px-6 py-4 text-left">Nama</th>
                    <th class="px-6 py-4 text-left">NIM</th>
                    <th class="px-6 py-4 text-left">Email</th>
                    <th class="px-6 py-4 text-left">Booking</th>
                    <th class="px-6 py-4 text-left">Role</th>
                    <th class="px-6 py-4 text-left">Ubah Role</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($users as $i => $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-gray-400 font-mono text-xs">{{ $users->firstItem() + $i }}</td>

                        {{-- Nama --}}
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                                    {{ $user->is_super_admin ? 'bg-yellow-100 text-yellow-700' : ($user->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="font-semibold text-gray-800 flex items-center gap-1.5">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 border border-indigo-200 px-1.5 py-0.5 rounded-full">Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 font-mono text-gray-500 text-xs">{{ $user->nim }}</td>
                        <td class="px-6 py-4 text-gray-600 text-xs">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-gray-500 text-xs">{{ $user->bookings_count }}x</td>

                        {{-- Badge Role --}}
                        <td class="px-6 py-4">
                            @if($user->is_super_admin)
                                <span class="px-2.5 py-1 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full text-xs font-bold">⭐ Super Admin</span>
                            @elseif($user->role === 'admin')
                                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-full text-xs font-bold">🔒 Admin</span>
                            @else
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-600 border border-gray-200 rounded-full text-xs font-semibold">👤 Mahasiswa</span>
                            @endif
                        </td>

                        {{-- Ubah Role --}}
                        <td class="px-6 py-4">
                            @php
                                $isSelf       = $user->id === auth()->id();
                                $isSuperAdmin = $user->is_super_admin;
                                $actorIsSuper = auth()->user()->is_super_admin;
                                $canPromote   = !$isSelf && !$isSuperAdmin && $user->role === 'mahasiswa';
                                $canDemote    = !$isSelf && !$isSuperAdmin && $user->role === 'admin' && $actorIsSuper;
                            @endphp

                            @if($isSelf || $isSuperAdmin)
                                <span class="text-xs text-gray-300 italic">—</span>
                            @elseif($canPromote)
                                <form method="POST" action="{{ route('admin.users.update-role', $user) }}"
                                      onsubmit="return confirm('Promosikan {{ $user->name }} menjadi Admin?')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="role" value="admin">
                                    <button class="px-3 py-1.5 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">↑ Jadi Admin</button>
                                </form>
                            @elseif($canDemote)
                                <form method="POST" action="{{ route('admin.users.update-role', $user) }}"
                                      onsubmit="return confirm('Turunkan {{ $user->name }} ke Mahasiswa?')">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="role" value="mahasiswa">
                                    <button class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 rounded-lg transition-colors">↓ Turunkan</button>
                                </form>
                            @else
                                <span class="text-xs text-gray-300 italic">Sudah Admin</span>
                            @endif
                        </td>

                        {{-- Aksi Edit / Hapus --}}
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
                                    Edit
                                </a>
                                @if(!$user->is_super_admin && $user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                          onsubmit="return confirm('Hapus akun {{ $user->name }}? Semua booking terkait juga akan terhapus.')">
                                        @csrf @method('DELETE')
                                        <button class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-400">Tidak ada data user ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">{{ $users->links() }}</div>
        @endif
    </div>
</x-app-layout>
