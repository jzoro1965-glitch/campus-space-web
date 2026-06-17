<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bookings.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Buat Booking Manual</h2>
                <p class="text-xs text-gray-400 mt-0.5">Admin membuat booking meja atas nama mahasiswa</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-2xl" x-data="{ selectedDesk: null, selectedDeskCode: '' }">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
            <form method="POST" action="{{ route('admin.bookings.store') }}" class="space-y-5">
                @csrf

                {{-- Pilih Mahasiswa --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Mahasiswa <span class="text-red-500">*</span>
                    </label>
                    <select name="user_id"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('user_id') border-red-400 bg-red-50 @enderror">
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach($mahasiswas as $mhs)
                            <option value="{{ $mhs->id }}" {{ old('user_id') == $mhs->id ? 'selected' : '' }}>
                                {{ $mhs->name }} ({{ $mhs->nim }}) — {{ $mhs->email }}
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    @if($mahasiswas->isEmpty())
                        <p class="mt-1 text-xs text-amber-600">
                            Belum ada data mahasiswa.
                            <a href="{{ route('admin.users.create') }}" class="underline font-medium">Tambah mahasiswa dulu</a>.
                        </p>
                    @endif
                </div>

                {{-- Pilih Meja --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Pilih Meja <span class="text-red-500">*</span>
                    </label>
                    <input type="hidden" name="desk_id" :value="selectedDesk">
                    <div class="grid grid-cols-4 sm:grid-cols-6 gap-3">
                        @foreach($desks as $desk)
                            <div @click="selectedDesk = {{ $desk->id }}; selectedDeskCode = '{{ $desk->code }}'"
                                 :class="selectedDesk === {{ $desk->id }}
                                    ? 'bg-indigo-600 border-indigo-700 text-white scale-105 shadow-md'
                                    : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-indigo-50 hover:border-indigo-300'"
                                 class="p-3 rounded-xl border-2 cursor-pointer text-center transition-all duration-150 select-none">
                                <div class="text-[10px] text-current opacity-60 uppercase font-semibold">{{ $desk->location }}</div>
                                <div class="text-base font-black mt-0.5">{{ $desk->code }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-2 text-xs" x-show="selectedDesk">
                        <span class="text-indigo-600 font-semibold">Meja dipilih: </span>
                        <span class="font-bold" x-text="selectedDeskCode"></span>
                    </div>
                    @error('desk_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tanggal & Jam --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Tanggal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="booking_date"
                               value="{{ old('booking_date', $today) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('booking_date') border-red-400 bg-red-50 @enderror">
                        @error('booking_date')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="start_time"
                               value="{{ old('start_time', '08:00') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('start_time') border-red-400 bg-red-50 @enderror">
                        @error('start_time')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="end_time"
                               value="{{ old('end_time', '10:00') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('end_time') border-red-400 bg-red-50 @enderror">
                        @error('end_time')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tombol --}}
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                            :disabled="!selectedDesk"
                            :class="!selectedDesk ? 'opacity-50 cursor-not-allowed bg-indigo-400' : 'bg-indigo-600 hover:bg-indigo-700'"
                            class="px-6 py-2.5 text-white text-sm font-semibold rounded-xl transition-colors">
                        Buat Booking
                    </button>
                    <a href="{{ route('admin.bookings.index') }}"
                       class="px-6 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
