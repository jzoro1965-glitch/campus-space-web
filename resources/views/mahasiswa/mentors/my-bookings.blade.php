<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Booking Mentor Saya</h2>
                <p class="text-xs text-gray-400 mt-0.5">Riwayat semua sesi mentoring yang pernah Anda booking</p>
            </div>
            <a href="{{ route('mahasiswa.mentors.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl transition-colors">
                + Booking Baru
            </a>
        </div>
    </x-slot>

    @if($bookings->isEmpty())
        <div class="text-center py-20 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <p class="text-sm font-medium mb-3">Belum ada booking mentor.</p>
            <a href="{{ route('mahasiswa.mentors.index') }}" class="text-indigo-600 text-sm font-semibold hover:underline">Booking sekarang →</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($bookings as $b)
            @php $color = $b->status_color; @endphp
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-start justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-indigo-100 flex items-center justify-center text-lg font-black text-indigo-700 shrink-0">
                            {{ strtoupper(substr($b->mentor->name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $b->mentor->name ?? '—' }}</p>
                            <p class="text-xs text-gray-500">{{ $b->mentor->expertise ?? '' }}</p>
                            @if($b->schedule)
                                <p class="text-xs text-indigo-600 font-semibold mt-1">
                                    {{ $b->schedule->date->format('d M Y') }}
                                    · {{ substr($b->schedule->start_time,0,5) }} – {{ substr($b->schedule->end_time,0,5) }} WIB
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-lg font-black text-gray-900">{{ $b->formatted_amount }}</p>
                        <span class="inline-block mt-1 px-2.5 py-1 rounded-full text-xs font-bold
                            bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
                            {{ $b->status_label }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                    <span class="font-mono text-xs text-gray-400">{{ $b->order_id }}</span>
                    <div class="flex gap-2">
                        @if($b->status === 'pending' && $b->snap_token)
                            <button onclick="payNow('{{ $b->snap_token }}')"
                                    class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-colors">
                                Bayar Sekarang
                            </button>
                        @endif
                        @if($b->status === 'pending')
                            <form method="POST" action="{{ route('mahasiswa.mentors.booking.cancel', $b) }}"
                                  onsubmit="return confirm('Batalkan booking ini?')">
                                @csrf @method('DELETE')
                                <button class="px-4 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 text-xs font-bold rounded-lg transition-colors">
                                    Batalkan
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('mahasiswa.mentors.booking.show', $b) }}"
                           class="px-4 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-xs font-semibold rounded-lg transition-colors">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @if($bookings->hasPages())
            <div class="mt-6">{{ $bookings->links() }}</div>
        @endif
    @endif

    @if($bookings->where('status','pending')->where('snap_token','!=',null)->count())
        <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
        <script>
            function payNow(token) {
                snap.pay(token, {
                    onSuccess: () => location.reload(),
                    onPending: () => location.reload(),
                    onError:   () => alert('Pembayaran gagal.'),
                });
            }
        </script>
    @endif
</x-app-layout>
