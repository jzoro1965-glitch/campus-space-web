<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Booking Sesi Mentor</h2>
                <p class="text-xs text-gray-400 mt-0.5">Pilih mentor, tentukan jadwal, bayar — sesi Anda terkonfirmasi otomatis</p>
            </div>
            <a href="{{ route('mahasiswa.mentors.my-bookings') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Booking Saya
            </a>
        </div>
    </x-slot>

    {{-- Filter --}}
    <form method="GET" action="{{ route('mahasiswa.mentors.index') }}" class="flex flex-wrap items-center gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau keahlian mentor..."
               class="px-4 py-2 border border-gray-300 rounded-xl text-sm w-64 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        @if($expertiseList->count())
        <select name="expertise" class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Keahlian</option>
            @foreach($expertiseList as $ex)
                <option value="{{ $ex }}" {{ request('expertise') === $ex ? 'selected' : '' }}>{{ $ex }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">Cari</button>
        @if(request()->hasAny(['search','expertise']))
            <a href="{{ route('mahasiswa.mentors.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-colors">Reset</a>
        @endif
    </form>

    {{-- Grid mentor --}}
    @if($mentors->isEmpty())
        <div class="text-center py-20 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            <p class="text-sm font-medium">Tidak ada mentor ditemukan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($mentors as $mentor)
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">
                {{-- Header warna --}}
                <div class="bg-gradient-to-br from-indigo-600 to-violet-700 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center text-xl font-black text-white">
                            {{ strtoupper(substr($mentor->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-bold text-white text-base">{{ $mentor->name }}</h3>
                            <p class="text-indigo-200 text-xs mt-0.5">{{ $mentor->total_sessions }} sesi selesai</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-4">
                    {{-- Keahlian --}}
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(array_map('trim', explode(',', $mentor->expertise)) as $tag)
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-full border border-indigo-100">{{ $tag }}</span>
                        @endforeach
                    </div>

                    {{-- Bio --}}
                    @if($mentor->bio)
                        <p class="text-sm text-gray-500 line-clamp-2">{{ $mentor->bio }}</p>
                    @endif

                    {{-- Info --}}
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-black text-gray-900 text-lg">{{ $mentor->formatted_price }}</span>
                        <span class="text-gray-400 text-xs">/ {{ $mentor->session_duration_minutes }} menit</span>
                    </div>

                    <a href="{{ route('mahasiswa.mentors.show', $mentor) }}"
                       class="block w-full text-center py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors">
                        Lihat Jadwal & Booking →
                    </a>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
