<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.mentors.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Jadwal — {{ $mentor->name }}</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ $mentor->expertise }} · {{ $mentor->formatted_price }} / sesi</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Form tambah jadwal --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-base font-bold text-gray-800 mb-4">Tambah Jadwal Baru</h3>
            <form method="POST" action="{{ route('admin.mentors.schedules.store', $mentor) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal <span class="text-red-500">*</span></label>
                    <input type="date" name="date" value="{{ old('date', now()->addDay()->format('Y-m-d')) }}"
                           min="{{ now()->format('Y-m-d') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Mulai</label>
                        <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Selesai</label>
                        <input type="time" name="end_time" value="{{ old('end_time', '10:00') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Pengulangan</label>
                    <select name="repeat" id="repeat"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                            onchange="document.getElementById('until-wrap').classList.toggle('hidden', this.value==='none')">
                        <option value="none">Tidak ada (sekali)</option>
                        <option value="daily">Setiap hari</option>
                        <option value="weekly">Setiap minggu</option>
                    </select>
                </div>
                <div id="until-wrap" class="hidden">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Sampai tanggal</label>
                    <input type="date" name="repeat_until" value="{{ old('repeat_until') }}"
                           min="{{ now()->addDay()->format('Y-m-d') }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors">
                    + Tambah Jadwal
                </button>
            </form>
        </div>

        {{-- Daftar jadwal --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-800">Semua Jadwal</h3>
                <span class="text-xs text-gray-400">{{ $schedules->total() }} slot</span>
            </div>
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-5 py-3 text-left">Tanggal</th>
                        <th class="px-5 py-3 text-left">Jam</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($schedules as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <p class="font-semibold text-gray-800">{{ $s->date->format('d M Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $s->date->translatedFormat('l') }}</p>
                        </td>
                        <td class="px-5 py-3 font-mono text-gray-700">
                            {{ substr($s->start_time,0,5) }} – {{ substr($s->end_time,0,5) }}
                        </td>
                        <td class="px-5 py-3">
                            @if($s->is_booked)
                                <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full text-xs font-semibold">Ter-booking</span>
                            @elseif($s->date->lt(now()->toDateString()))
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-500 rounded-full text-xs font-semibold">Lewat</span>
                            @else
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-semibold">Tersedia</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if(! $s->is_booked)
                                <form method="POST" action="{{ route('admin.mentors.schedules.destroy', $s) }}"
                                      onsubmit="return confirm('Hapus jadwal ini?')">
                                    @csrf @method('DELETE')
                                    <button class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-gray-400 text-sm">
                            Belum ada jadwal. Tambahkan dari form di sebelah kiri.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($schedules->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">{{ $schedules->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
