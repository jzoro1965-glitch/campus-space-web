<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Semua Booking</h2>
                <p class="text-xs text-gray-400 mt-0.5">Monitor dan kelola seluruh data booking mahasiswa</p>
            </div>
            {{-- Filter Form --}}
            <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex items-center gap-3">
                <input type="date" name="date" value="{{ request('date') }}"
                       class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Approved</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">Filter</button>
                @if(request()->hasAny(['date','status']))
                    <a href="{{ route('admin.bookings.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-colors">Reset</a>
                @endif
            </form>
        </div>
    </x-slot>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-4 text-left">Mahasiswa</th>
                    <th class="px-6 py-4 text-left">NIM</th>
                    <th class="px-6 py-4 text-left">Meja</th>
                    <th class="px-6 py-4 text-left">Tanggal</th>
                    <th class="px-6 py-4 text-left">Jam</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                @forelse($bookings as $booking)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-800">{{ $booking->user->name ?? '-' }}</div>
                            <div class="text-xs text-gray-400">{{ $booking->user->email ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 font-mono text-gray-600 text-xs">{{ $booking->user->nim ?? '-' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-lg text-xs font-bold">
                                {{ $booking->desk->code ?? '-' }}
                            </span>
                            <div class="text-xs text-gray-400 mt-0.5">{{ $booking->desk->location ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-gray-700 font-mono text-xs">
                            {{ substr($booking->start_time, 0, 5) }} – {{ substr($booking->end_time, 0, 5) }}
                        </td>
                        <td class="px-6 py-4">
                            @if($booking->status === 'approved')
                                <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full text-xs font-semibold">Approved</span>
                            @elseif($booking->status === 'cancelled')
                                <span class="px-2.5 py-1 bg-red-50 text-red-700 border border-red-200 rounded-full text-xs font-semibold">Cancelled</span>
                            @else
                                <span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-full text-xs font-semibold">{{ $booking->status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($booking->status === 'approved')
                                    <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" onclick="return confirm('Batalkan booking ini?')"
                                                class="px-3 py-1.5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100 transition-colors">
                                            Batalkan
                                        </button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('admin.bookings.destroy', $booking) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Hapus data booking ini permanen?')"
                                            class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                            Tidak ada data booking yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($bookings->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
