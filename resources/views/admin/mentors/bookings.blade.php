<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.mentors.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800">Semua Booking Mentor</h2>
                <p class="text-xs text-gray-400 mt-0.5">Monitor dan kelola transaksi sesi mentoring</p>
            </div>
        </div>
    </x-slot>

    {{-- Filter --}}
    <form method="GET" action="{{ route('admin.mentors.bookings') }}" class="flex flex-wrap items-center gap-3 mb-5">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / NIM mahasiswa..."
               class="px-4 py-2 border border-gray-300 rounded-xl text-sm w-60 focus:outline-none focus:ring-2 focus:ring-indigo-500">
        <select name="mentor_id" class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Mentor</option>
            @foreach($mentors as $m)
                <option value="{{ $m->id }}" {{ request('mentor_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
            @endforeach
        </select>
        <select name="status" class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">Semua Status</option>
            @foreach(['pending'=>'Pending','paid'=>'Paid','completed'=>'Completed','cancelled'=>'Cancelled','failed'=>'Failed','expired'=>'Expired'] as $v=>$l)
                <option value="{{ $v }}" {{ request('status') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-xl hover:bg-indigo-700 transition-colors">Filter</button>
        @if(request()->hasAny(['search','status','mentor_id']))
            <a href="{{ route('admin.mentors.bookings') }}" class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-semibold rounded-xl hover:bg-gray-200 transition-colors">Reset</a>
        @endif
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-100 text-sm">
            <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                <tr>
                    <th class="px-5 py-4 text-left">Mahasiswa</th>
                    <th class="px-5 py-4 text-left">Mentor</th>
                    <th class="px-5 py-4 text-left">Jadwal Sesi</th>
                    <th class="px-5 py-4 text-left">Nominal</th>
                    <th class="px-5 py-4 text-left">Status & Pembayaran</th>
                    <th class="px-5 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($bookings as $b)
                @php $color = $b->status_color; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-4">
                        <p class="font-semibold text-gray-800">{{ $b->user->name ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $b->user->nim ?? '' }}</p>
                        <p class="text-xs text-gray-400">{{ $b->user->email ?? '' }}</p>
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-gray-800">{{ $b->mentor->name ?? '-' }}</p>
                        <p class="text-xs text-gray-400">{{ $b->mentor->expertise ?? '' }}</p>
                    </td>
                    <td class="px-5 py-4 text-xs text-gray-600">
                        @if($b->schedule)
                            <p class="font-semibold text-gray-800">{{ $b->schedule->date->format('d M Y') }}</p>
                            <p>{{ substr($b->schedule->start_time,0,5) }} – {{ substr($b->schedule->end_time,0,5) }} WIB</p>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-bold text-gray-800">{{ $b->formatted_amount }}</p>
                        @if($b->payment_type)
                            <p class="text-xs text-gray-400 mt-0.5">{{ ucfirst(str_replace('_',' ',$b->payment_type)) }}</p>
                        @endif
                        @if($b->paid_at)
                            <p class="text-xs text-emerald-600 mt-0.5">Bayar: {{ $b->paid_at->format('d M Y H:i') }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        {{-- Badge status --}}
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold mb-2
                            bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
                            {{ $b->status_label }}
                        </span>

                        {{-- Snap token + link bayar untuk pending --}}
                        @if($b->status === 'pending' && $b->snap_token)
                            <div class="mt-1.5">
                                <button onclick="payNow('{{ $b->snap_token }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    Buka Payment
                                </button>
                                {{-- Link langsung ke Midtrans untuk dibagikan ke mahasiswa --}}
                                <a href="https://app.midtrans.com/snap/v2/vtweb/{{ $b->snap_token }}"
                                   target="_blank"
                                   class="inline-flex items-center gap-1 mt-1.5 text-xs text-indigo-500 hover:text-indigo-700 underline">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    Link pembayaran →
                                </a>
                                <p class="text-[10px] text-gray-400 mt-0.5 font-mono break-all">{{ Str::limit($b->snap_token, 20) }}...</p>
                            </div>
                        @endif

                        {{-- Order ID --}}
                        <p class="text-[10px] text-gray-400 font-mono mt-1">{{ $b->order_id }}</p>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex flex-col items-end gap-2">
                            @if($b->status === 'paid')
                                <form method="POST" action="{{ route('admin.mentors.bookings.complete', $b) }}">
                                    @csrf @method('PATCH')
                                    <button class="px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition-colors">
                                        ✓ Selesai
                                    </button>
                                </form>
                            @endif
                            @if(in_array($b->status, ['pending','paid']))
                                <form method="POST" action="{{ route('admin.mentors.bookings.cancel', $b) }}"
                                      onsubmit="return confirm('Batalkan booking ini? Slot jadwal akan dibebaskan.')">
                                    @csrf @method('PATCH')
                                    <button class="px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition-colors">
                                        Batalkan
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Belum ada booking mentor.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($bookings->hasPages())
            <div class="px-5 py-4 border-t border-gray-100">{{ $bookings->links() }}</div>
        @endif
    </div>

    {{-- Midtrans Snap untuk admin bisa buka payment langsung --}}
    @if($bookings->where('status','pending')->where('snap_token','!=',null)->count())
        <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
        <script>
            function payNow(token) {
                if (typeof snap === 'undefined') {
                    alert('Payment gateway sedang loading. Coba lagi dalam beberapa detik, atau gunakan "Link pembayaran" di bawahnya.');
                    return;
                }
                snap.pay(token, {
                    onSuccess: function(result) {
                        alert('Pembayaran berhasil!');
                        location.reload();
                    },
                    onPending: function(result) {
                        alert('Menunggu pembayaran.');
                        location.reload();
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal: ' + (result.status_message || 'Silakan cek log Midtrans.'));
                    },
                    onClose: function() {
                        location.reload();
                    }
                });
            }
        </script>
    @endif
</x-app-layout>
