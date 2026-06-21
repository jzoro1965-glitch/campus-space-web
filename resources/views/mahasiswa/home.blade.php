<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Booking Meja Belajar</h2>
                <p class="text-xs text-gray-400 mt-0.5">Pilih meja yang tersedia dan atur jadwal Anda</p>
            </div>
            {{-- Badge status hari ini --}}
            @if($sudahBookingHariIni)
                <span class="px-3 py-1.5 text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-full flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Anda sudah memiliki booking aktif hari ini
                </span>
            @else
                <span class="px-3 py-1.5 text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Belum ada booking hari ini
                </span>
            @endif
        </div>
    </x-slot>

    <div class="space-y-6" x-data="bookingForm()">

        {{-- ──────────────────────────────────────────────────── --}}
        {{-- BANNER KUOTA PAKET                                  --}}
        {{-- ──────────────────────────────────────────────────── --}}
        @if($activePayment)
            <div class="flex items-center justify-between flex-wrap gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-800">{{ $activePayment->plan->name }} — Aktif</p>
                        <p class="text-xs text-emerald-600 mt-0.5">
                            <span class="font-black text-emerald-700">{{ $activePayment->quota_remaining }}</span> booking tersisa
                            · berlaku s/d <strong>{{ $activePayment->active_until->format('d M Y') }}</strong>
                        </p>
                    </div>
                </div>
                <a href="{{ route('mahasiswa.payments.index') }}"
                   class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 underline">
                    Kelola Paket →
                </a>
            </div>
        @else
            <div class="flex items-center justify-between flex-wrap gap-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-amber-800">Anda belum memiliki paket aktif</p>
                        <p class="text-xs text-amber-600 mt-0.5">Beli paket terlebih dahulu untuk bisa melakukan booking</p>
                    </div>
                </div>
                <a href="{{ route('mahasiswa.payments.index') }}"
                   class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition-colors">
                    Beli Paket Sekarang
                </a>
            </div>
        @endif

        {{-- ──────────────────────────────────────────────────── --}}
        {{-- PANEL ATURAN BOOKING                                --}}
        {{-- ──────────────────────────────────────────────────── --}}
        <div class="bg-indigo-50 border border-indigo-200 rounded-2xl p-4">            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-bold text-indigo-800 mb-2">Aturan Booking</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-x-8 gap-y-1 text-xs text-indigo-700">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Jam operasional: <strong>07:00 – 21:00 WIB</strong>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Maksimal durasi: <strong>3 jam per sesi</strong>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Maksimal: <strong>1 booking aktif per hari</strong>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ──────────────────────────────────────────────────── --}}
        {{-- BAGIAN 1: DENAH MEJA                                --}}
        {{-- ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" id="desk-map">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Pilih Meja</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Klik meja yang tersedia (hijau) untuk memilihnya</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-medium">
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-emerald-500"></span> Tersedia</span>
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-red-400"></span> Ter-booking</span>
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-gray-400"></span> Nonaktif</span>
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-indigo-500"></span> Dipilih</span>
                </div>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                @foreach($desks as $desk)
                    @php
                        $isBooked   = $bookedDeskIds->contains($desk->id);
                        $isInactive = !$desk->is_active;
                    @endphp

                    @if($isInactive)
                        {{-- Meja nonaktif --}}
                        <div class="p-4 rounded-2xl border bg-gray-50 border-gray-200 text-gray-400 opacity-60 cursor-not-allowed select-none">
                            <div class="text-[10px] uppercase font-semibold text-gray-300 mb-1">{{ $desk->location }}</div>
                            <div class="text-lg font-black">{{ $desk->code }}</div>
                            <div class="mt-2 text-[10px] font-bold uppercase bg-gray-200 text-gray-500 px-2 py-0.5 rounded-full inline-block">Nonaktif</div>
                        </div>
                    @elseif($isBooked)
                        {{-- Meja ter-booking: tidak bisa diklik --}}
                        <div class="p-4 rounded-2xl border bg-red-50 border-red-200 text-red-800 opacity-70 cursor-not-allowed select-none">
                            <div class="text-[10px] uppercase font-semibold text-gray-400 mb-1">{{ $desk->location }}</div>
                            <div class="text-lg font-black">{{ $desk->code }}</div>
                            <div class="mt-2 text-[10px] font-bold uppercase bg-red-200/60 text-red-800 px-2 py-0.5 rounded-full inline-block">Ter-booking</div>
                        </div>
                    @else
                        {{-- Meja tersedia: bisa diklik --}}
                        <div @click="selectDesk({{ $desk->id }}, '{{ $desk->code }}')"
                             :class="selectedDeskId === {{ $desk->id }}
                                ? 'bg-indigo-600 border-indigo-700 text-white scale-105 shadow-lg'
                                : 'bg-emerald-50 border-emerald-200 text-emerald-900 hover:scale-105 hover:shadow-md'"
                             class="p-4 rounded-2xl border transition-all duration-200 cursor-pointer select-none">
                            <div class="text-[10px] uppercase font-semibold opacity-60 mb-1">{{ $desk->location }}</div>
                            <div class="text-lg font-black">{{ $desk->code }}</div>
                            <div class="mt-2 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full inline-block"
                                 :class="selectedDeskId === {{ $desk->id }} ? 'bg-indigo-500 text-indigo-100' : 'bg-emerald-200/60 text-emerald-800'">
                                <span x-show="selectedDeskId !== {{ $desk->id }}">Tersedia</span>
                                <span x-show="selectedDeskId === {{ $desk->id }}">✓ Dipilih</span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ──────────────────────────────────────────────────── --}}
        {{-- BAGIAN 2: FORM BOOKING                              --}}
        {{-- ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" id="booking-form">
            <h3 class="text-lg font-bold text-gray-800 mb-5">Form Booking</h3>

            @if($sudahBookingHariIni)
                <div class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-800 mb-5">
                    <svg class="w-5 h-5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Anda sudah memiliki <strong>1 booking aktif hari ini</strong>. Booking baru hanya bisa dilakukan untuk tanggal lain, atau batalkan booking hari ini terlebih dahulu.</span>
                </div>
            @endif

            <form method="POST" action="{{ route('mahasiswa.bookings.store') }}" class="space-y-5">
                @csrf

                {{-- Hidden: desk_id diisi otomatis saat klik meja --}}
                <input type="hidden" name="desk_id" :value="selectedDeskId">

                {{-- Meja yang dipilih --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Meja Dipilih</label>
                    <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl">
                        <template x-if="selectedDeskId">
                            <span class="px-3 py-1 bg-indigo-600 text-white text-sm font-bold rounded-lg" x-text="selectedDeskCode"></span>
                        </template>
                        <template x-if="!selectedDeskId">
                            <span class="text-sm text-gray-400 italic">Klik meja di atas untuk memilih...</span>
                        </template>
                    </div>
                    @error('desk_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                    {{-- Tanggal Booking --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                        <input type="date" name="booking_date" value="{{ old('booking_date', $today) }}"
                               min="{{ $today }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('booking_date') border-red-400 bg-red-50 @enderror">
                        @error('booking_date')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Mulai --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jam Mulai <span class="text-red-500">*</span></label>
                        <input type="time" name="start_time" value="{{ old('start_time', '08:00') }}"
                               min="07:00" max="20:00"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('start_time') border-red-400 bg-red-50 @enderror">
                        @error('start_time')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Selesai --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" name="end_time" value="{{ old('end_time', '10:00') }}"
                               min="07:00" max="21:00"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('end_time') border-red-400 bg-red-50 @enderror">
                        @error('end_time')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            :disabled="!selectedDeskId"
                            :class="!selectedDeskId ? 'opacity-50 cursor-not-allowed bg-indigo-400' : 'bg-indigo-600 hover:bg-indigo-700'"
                            class="inline-flex items-center gap-2 px-6 py-2.5 text-white text-sm font-semibold rounded-xl transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Konfirmasi Booking
                    </button>
                    <p class="text-xs text-gray-400" x-show="!selectedDeskId">← Pilih meja terlebih dahulu</p>
                </div>
            </form>
        </div>

        {{-- ──────────────────────────────────────────────────── --}}
        {{-- BAGIAN 3: BOOKING HARI INI (semua mahasiswa)        --}}
        {{-- ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Aktivitas Hari Ini</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ \Carbon\Carbon::parse($today)->translatedFormat('l, d F Y') }} — {{ $todayBookings->count() }} booking aktif</p>
                </div>
                <span class="flex items-center gap-1.5 text-xs text-emerald-600 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Live
                </span>
            </div>

            @if($todayBookings->isEmpty())
                <div class="text-center py-8 text-gray-400">
                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm font-medium">Belum ada yang booking hari ini.</p>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($todayBookings as $b)
                        @php $isMine = $b->user_id === auth()->id(); @endphp
                        <div class="flex items-center gap-3 p-3 rounded-xl border
                            {{ $isMine ? 'bg-indigo-50 border-indigo-200' : 'bg-gray-50 border-gray-200' }}">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xs font-black shrink-0
                                {{ $isMine ? 'bg-indigo-200 text-indigo-800' : 'bg-gray-200 text-gray-600' }}">
                                {{ $b->desk->code }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate">
                                    {{ $b->user->name }}
                                    @if($isMine)
                                        <span class="text-[10px] font-bold text-indigo-600 bg-indigo-100 px-1.5 py-0.5 rounded-full ml-1">Anda</span>
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">{{ $b->desk->location }} · {{ substr($b->start_time, 0, 5) }}–{{ substr($b->end_time, 0, 5) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ──────────────────────────────────────────────────── --}}
        {{-- BAGIAN 4: RIWAYAT BOOKING SAYA                      --}}
        {{-- ──────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-5">Riwayat Booking Saya</h3>

            @if($myBookings->isEmpty())
                <div class="text-center py-10 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    <p class="text-sm font-medium">Belum ada riwayat booking.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($myBookings as $booking)
                        <div class="flex items-center justify-between p-4 rounded-xl border
                            {{ $booking->status === 'approved'
                                ? 'bg-emerald-50 border-emerald-200'
                                : ($booking->status === 'expired'
                                    ? 'bg-orange-50 border-orange-200 opacity-70'
                                    : 'bg-gray-50 border-gray-200 opacity-60') }}">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-sm font-black
                                    {{ $booking->status === 'approved'
                                        ? 'bg-emerald-200 text-emerald-800'
                                        : ($booking->status === 'expired'
                                            ? 'bg-orange-200 text-orange-800'
                                            : 'bg-gray-200 text-gray-600') }}">
                                    {{ $booking->desk->code }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800">{{ $booking->desk->location }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}
                                        &nbsp;·&nbsp;
                                        {{ substr($booking->start_time, 0, 5) }} – {{ substr($booking->end_time, 0, 5) }} WIB
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                @if($booking->status === 'approved')
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-full text-xs font-semibold">Aktif</span>
                                    <form method="POST" action="{{ route('mahasiswa.bookings.cancel', $booking) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Batalkan booking meja {{ $booking->desk->code }}?')"
                                                class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                            Batalkan
                                        </button>
                                    </form>
                                @elseif($booking->status === 'expired')
                                    <span class="px-2.5 py-1 bg-orange-50 text-orange-700 border border-orange-200 rounded-full text-xs font-semibold">Expired</span>
                                @else
                                    <span class="px-2.5 py-1 bg-gray-100 text-gray-500 border border-gray-200 rounded-full text-xs font-semibold">Dibatalkan</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>{{-- end x-data --}}

    {{-- Scroll otomatis ke form setelah error --}}
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.getElementById('booking-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        </script>
    @endif

    <script>
        function bookingForm() {
            return {
                selectedDeskId: {{ old('desk_id') ?: 'null' }},
                selectedDeskCode: '{{ old('desk_id') ? '' : '' }}',
                selectDesk(id, code) {
                    if (this.selectedDeskId === id) {
                        this.selectedDeskId = null;
                        this.selectedDeskCode = '';
                    } else {
                        this.selectedDeskId = id;
                        this.selectedDeskCode = code;
                        // Scroll ke form saat meja dipilih
                        this.$nextTick(() => {
                            document.getElementById('booking-form')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        });
                    }
                }
            }
        }
    </script>
</x-app-layout>
