<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
                {{ __('Dashboard Monitoring Study Space') }}
            </h2>
            <span class="px-3 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-full flex items-center gap-1.5 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                Sistem Live
            </span>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- ====================================================== --}}
            {{-- BAGIAN 1: KARTU STATISTIK                              --}}
            {{-- ====================================================== --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                {{-- Card: Total Meja --}}
                <div class="p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center justify-between transition-all hover:shadow-md">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Meja</p>
                        <h4 class="text-3xl font-extrabold text-gray-800 mt-1">{{ $desks->count() }}</h4>
                    </div>
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                </div>

                {{-- Card: Booking Aktif Hari Ini --}}
                <div class="p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center justify-between transition-all hover:shadow-md">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Booking Aktif Hari Ini</p>
                        <h4 class="text-3xl font-extrabold text-red-600 mt-1">{{ $activeBookings->count() }}</h4>
                    </div>
                    <div class="p-3 bg-red-50 text-red-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                </div>

                {{-- Card: Meja Tersedia --}}
                <div class="p-6 bg-white border border-gray-100 rounded-2xl shadow-sm flex items-center justify-between transition-all hover:shadow-md">
                    <div>
                        <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">Meja Tersedia</p>
                        <h4 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $desks->count() - $activeBookings->count() }}</h4>
                    </div>
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

            </div>

            {{-- ====================================================== --}}
            {{-- BAGIAN 2: DENAH VISUAL INTERAKTIF (GRID MEJA)          --}}
            {{-- ====================================================== --}}
            <div class="p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">Denah Visual Meja</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Status ketersediaan meja secara real-time hari ini</p>
                    </div>
                    <div class="flex items-center gap-4 text-xs font-medium">
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <span class="w-3 h-3 rounded bg-emerald-500"></span> Kosong
                        </span>
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <span class="w-3 h-3 rounded bg-red-500"></span> Ter-Booking
                        </span>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-5">
                    @foreach($desks as $desk)
                        @php
                            // Cek apakah desk_id meja ini ada di dalam koleksi bookings hari ini
                            $isBooked = $activeBookings->contains('desk_id', $desk->id);
                        @endphp

                        @if($isBooked)
                            {{-- Kotak Merah: Status TER-BOOKING --}}
                            <div class="relative group p-5 rounded-2xl border transition-all duration-300 transform hover:-translate-y-1 hover:shadow-md bg-red-50 border-red-200 text-red-900 shadow-sm">
                                <div class="absolute top-3 right-3">
                                    <span class="flex h-2 w-2 relative">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                    </span>
                                </div>
                                <div class="text-xs uppercase font-semibold tracking-wider text-gray-400 mb-1">{{ $desk->location }}</div>
                                <div class="text-xl font-black mb-2">{{ $desk->code }}</div>
                                <div class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-200/60 text-red-800">
                                    TER-BOOKING
                                </div>
                            </div>
                        @else
                            {{-- Kotak Hijau: Status KOSONG --}}
                            <div class="relative group p-5 rounded-2xl border transition-all duration-300 transform hover:-translate-y-1 hover:shadow-md bg-emerald-50 border-emerald-200 text-emerald-900 shadow-sm">
                                <div class="absolute top-3 right-3">
                                    <span class="flex h-2 w-2 relative">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                    </span>
                                </div>
                                <div class="text-xs uppercase font-semibold tracking-wider text-gray-400 mb-1">{{ $desk->location }}</div>
                                <div class="text-xl font-black mb-2">{{ $desk->code }}</div>
                                <div class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-200/60 text-emerald-800">
                                    KOSONG
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            {{-- ====================================================== --}}
            {{-- BAGIAN 3: TABEL LOG BOOKING HARI INI                   --}}
            {{-- ====================================================== --}}
            <div class="p-6 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-gray-800">Log Aktivitas Booking</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Daftar mahasiswa yang aktif menempati meja hari ini — data realtime dari database</p>
                </div>

                <div class="overflow-hidden border border-gray-100 rounded-xl">
                    <table class="min-w-full divide-y divide-gray-100 text-sm text-left">
                        <thead class="text-xs font-semibold text-gray-500 uppercase tracking-wider bg-gray-50">
                            <tr>
                                <th class="px-6 py-4">#</th>
                                <th class="px-6 py-4">Nama Mahasiswa</th>
                                <th class="px-6 py-4">NIM</th>
                                <th class="px-6 py-4">Kode Meja</th>
                                <th class="px-6 py-4">Sesi Jam Akses</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($activeBookings as $index => $booking)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-6 py-4 text-gray-400 font-mono text-xs">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900">{{ $booking->user->name ?? 'User Tidak Ditemukan' }}</div>
                                        <div class="text-xs text-gray-400">{{ $booking->user->email ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-gray-600">
                                        {{ $booking->user->nim ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                            {{ $booking->desk->code ?? 'Meja -' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            {{ substr($booking->start_time, 0, 5) }} – {{ substr($booking->end_time, 0, 5) }} WIB
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                        <div class="flex flex-col items-center justify-center space-y-2">
                                            <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                            <span class="text-sm font-medium">Belum ada aktivitas booking hari ini.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
