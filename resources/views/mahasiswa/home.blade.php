<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Booking Meja Belajar</h2>
            <p class="text-xs text-gray-400 mt-0.5">Pilih meja yang tersedia dan atur jadwal Anda</p>
        </div>
    </x-slot>

    <div class="space-y-8" x-data="bookingForm()">

        {{-- ──────────────────────────────────────────────── --}}
        {{-- BAGIAN 1: DENAH MEJA (pilih meja dari sini)     --}}
        {{-- ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Pilih Meja</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Klik meja yang tersedia (hijau) untuk memilihnya</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-medium">
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-emerald-500"></span> Tersedia</span>
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-red-400"></span> Ter-booking</span>
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-indigo-500"></span> Dipilih</span>
                </div>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                @foreach($desks as $desk)
                    @php $isBooked = $bookedDeskIds->contains($desk->id); @endphp

                    @if($isBooked)
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

        {{-- ──────────────────────────────────────────────── --}}
        {{-- BAGIAN 2: FORM BOOKING                          --}}
        {{-- ──────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-bold text-gray-800 mb-5">Form Booking</h3>

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
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('start_time') border-red-400 bg-red-50 @enderror">
                        @error('start_time')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Jam Selesai --}}
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jam Selesai <span class="text-red-500">*</span></label>
                        <input type="time" name="end_time" value="{{ old('end_time', '10:00') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('end_time') border-red-400 bg-red-50 @enderror">
                        @error('end_time')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <button type="submit"
                        :disabled="!selectedDeskId"
                        :class="!selectedDeskId ? 'opacity-50 cursor-not-allowed bg-indigo-400' : 'bg-indigo-600 hover:bg-indigo-700'"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-white text-sm font-semibold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Konfirmasi Booking
                </button>
            </form>
        </div>

        {{-- ──────────────────────────────────────────────── --}}
        {{-- BAGIAN 3: RIWAYAT BOOKING SAYA                  --}}
        {{-- ──────────────────────────────────────────────── --}}
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
                            {{ $booking->status === 'approved' ? 'bg-emerald-50 border-emerald-200' : 'bg-gray-50 border-gray-200 opacity-60' }}">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-sm font-black
                                    {{ $booking->status === 'approved' ? 'bg-emerald-200 text-emerald-800' : 'bg-gray-200 text-gray-600' }}">
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
                                    <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded-full text-xs font-semibold">Active</span>
                                    <form method="POST" action="{{ route('mahasiswa.bookings.cancel', $booking) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Batalkan booking meja {{ $booking->desk->code }}?')"
                                                class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                            Batalkan
                                        </button>
                                    </form>
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

    <script>
        function bookingForm() {
            return {
                selectedDeskId: {{ old('desk_id') ? old('desk_id') : 'null' }},
                selectedDeskCode: '{{ old('desk_id') ? '' : '' }}',
                selectDesk(id, code) {
                    if (this.selectedDeskId === id) {
                        this.selectedDeskId = null;
                        this.selectedDeskCode = '';
                    } else {
                        this.selectedDeskId = id;
                        this.selectedDeskCode = code;
                    }
                }
            }
        }
    </script>
</x-app-layout>
