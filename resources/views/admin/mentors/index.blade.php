<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Kelola Mentor</h2>
                <p class="text-xs text-gray-400 mt-0.5">Tambah, kelola profil, jadwal, dan booking sesi mentor</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.mentors.bookings') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Semua Booking
                </a>
                <a href="{{ route('admin.mentors.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Mentor
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        @foreach([
            ['Total Mentor',      $stats['total_mentors'],   'indigo'],
            ['Mentor Aktif',      $stats['active_mentors'],  'emerald'],
            ['Total Booking',     $stats['total_bookings'],  'blue'],
            ['Revenue',           'Rp '.number_format($stats['total_revenue'],0,',','.'), 'violet'],
            ['Menunggu Bayar',    $stats['pending_payment'], 'amber'],
        ] as [$label, $value, $color])
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ $label }}</p>
            <p class="text-xl font-extrabold text-{{ $color }}-600 mt-1">{{ $value }}</p>
        </div>
        @endforeach
    </div>

    {{-- Tabel mentor --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 text-left">Mentor</th>
                    <th class="px-6 py-4 text-left">Keahlian</th>
                    <th class="px-6 py-4 text-left">Harga / Sesi</th>
                    <th class="px-6 py-4 text-left">Durasi</th>
                    <th class="px-6 py-4 text-left">Slot Tersedia</th>
                    <th class="px-6 py-4 text-left">Total Booking</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($mentors as $mentor)
                <tr class="hover:bg-gray-50 {{ !$mentor->is_active ? 'opacity-60' : '' }}">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center text-sm font-black text-indigo-700 shrink-0">
                                {{ strtoupper(substr($mentor->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $mentor->name }}</p>
                                <p class="text-xs text-gray-400">{{ $mentor->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_map('trim', explode(',', $mentor->expertise)) as $tag)
                                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 text-[10px] font-semibold rounded-full border border-indigo-100">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-6 py-4 font-bold text-gray-800">{{ $mentor->formatted_price }}</td>
                    <td class="px-6 py-4 text-gray-600">{{ $mentor->session_duration_minutes }} menit</td>
                    <td class="px-6 py-4">
                        <span class="font-bold {{ $mentor->available_slots > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                            {{ $mentor->available_slots }} slot
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $mentor->total_bookings }}x</td>
                    <td class="px-6 py-4">
                        @if($mentor->is_active)
                            <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-semibold">Aktif</span>
                        @else
                            <span class="px-2.5 py-1 bg-gray-100 text-gray-500 border border-gray-200 rounded-full text-xs font-semibold">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.mentors.schedules', $mentor) }}"
                               class="px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                                Jadwal
                            </a>
                            <a href="{{ route('admin.mentors.edit', $mentor) }}"
                               class="px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.mentors.destroy', $mentor) }}"
                                  onsubmit="return confirm('Hapus mentor {{ $mentor->name }}?')">
                                @csrf @method('DELETE')
                                <button class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                        Belum ada mentor. <a href="{{ route('admin.mentors.create') }}" class="text-indigo-600 font-medium hover:underline">Tambah sekarang</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
