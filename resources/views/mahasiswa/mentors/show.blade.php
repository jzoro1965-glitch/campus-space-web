<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('mahasiswa.mentors.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ $mentor->name }}</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ $mentor->expertise }}</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="{ selectedSchedule: null, selectedLabel: '' }">

        {{-- Profil mentor --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="bg-gradient-to-br from-indigo-600 to-violet-700 px-6 py-6 text-white text-center">
                    <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center text-3xl font-black mx-auto mb-3">
                        {{ strtoupper(substr($mentor->name, 0, 1)) }}
                    </div>
                    <h3 class="font-black text-lg">{{ $mentor->name }}</h3>
                    <p class="text-indigo-200 text-sm mt-1">{{ $mentor->total_bookings }} sesi selesai</p>
                </div>
                <div class="px-6 py-5 space-y-3">
                    @if($mentor->bio)
                        <p class="text-sm text-gray-600">{{ $mentor->bio }}</p>
                    @endif
                    <div class="space-y-2 pt-2 border-t border-gray-100">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Harga</span>
                            <span class="font-bold text-gray-900">{{ $mentor->formatted_price }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Durasi</span>
                            <span class="font-semibold text-gray-700">{{ $mentor->session_duration_minutes }} menit</span>
                        </div>
                        @if($mentor->phone)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">WhatsApp</span>
                            <span class="font-semibold text-gray-700">{{ $mentor->phone }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        @foreach(array_map('trim', explode(',', $mentor->expertise)) as $tag)
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-full border border-indigo-100">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Pilih jadwal + form booking --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Jadwal tersedia --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Pilih Jadwal Tersedia</h3>

                @if($groupedSchedules->isEmpty())
                    <div class="text-center py-10 text-gray-400">
                        <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm">Belum ada jadwal tersedia. Coba lagi nanti.</p>
                    </div>
                @else
                    <div class="space-y-5">
                        @foreach($groupedSchedules as $dateStr => $slots)
                        @php $dateObj = \Carbon\Carbon::parse($dateStr); @endphp
                        <div>
                            <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                                {{ $dateObj->translatedFormat('l, d F Y') }}
                            </p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($slots as $slot)
                                <button type="button"
                                        @click="selectedSchedule = {{ $slot->id }}; selectedLabel = '{{ $dateObj->format('d M Y') }} · {{ substr($slot->start_time,0,5) }}–{{ substr($slot->end_time,0,5) }}'"
                                        :class="selectedSchedule === {{ $slot->id }}
                                            ? 'bg-indigo-600 text-white border-indigo-700 scale-105 shadow-md'
                                            : 'bg-white text-gray-700 border-gray-200 hover:border-indigo-400 hover:bg-indigo-50'"
                                        class="px-4 py-2 rounded-xl border text-sm font-semibold transition-all duration-150">
                                    {{ substr($slot->start_time,0,5) }} – {{ substr($slot->end_time,0,5) }}
                                </button>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Form booking --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Konfirmasi Booking</h3>

                <form method="POST" action="{{ route('mahasiswa.mentors.book', $mentor) }}">
                    @csrf
                    <input type="hidden" name="mentor_schedule_id" :value="selectedSchedule">

                    {{-- Jadwal dipilih --}}
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jadwal Dipilih</label>
                        <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm">
                            <template x-if="selectedSchedule">
                                <span class="font-bold text-indigo-700" x-text="selectedLabel"></span>
                            </template>
                            <template x-if="!selectedSchedule">
                                <span class="text-gray-400 italic">Pilih jadwal di atas terlebih dahulu...</span>
                            </template>
                        </div>
                        @error('mentor_schedule_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Catatan --}}
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan untuk Mentor (opsional)</label>
                        <textarea name="notes" rows="2" placeholder="Topik yang ingin dibahas, kesulitan belajar, dll..."
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Ringkasan harga --}}
                    <div class="bg-indigo-50 rounded-xl p-4 mb-5 flex items-center justify-between">
                        <span class="text-sm text-indigo-800 font-semibold">Total Pembayaran</span>
                        <span class="text-xl font-black text-indigo-900">{{ $mentor->formatted_price }}</span>
                    </div>

                    <button type="submit"
                            :disabled="!selectedSchedule"
                            :class="!selectedSchedule ? 'opacity-50 cursor-not-allowed bg-indigo-400' : 'bg-indigo-600 hover:bg-indigo-700'"
                            class="w-full py-3 text-white text-sm font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Lanjut ke Pembayaran
                    </button>
                    <p class="text-center text-xs text-gray-400 mt-2" x-show="!selectedSchedule">← Pilih jadwal dulu</p>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
