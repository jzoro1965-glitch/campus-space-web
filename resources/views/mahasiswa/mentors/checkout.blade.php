<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('mahasiswa.mentors.show', $mentor) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-bold text-gray-800">Checkout — Sesi Mentoring</h2>
        </div>
    </x-slot>

    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 px-6 py-6 text-white">
                <p class="text-indigo-200 text-xs uppercase tracking-wider mb-1">Sesi dengan</p>
                <h3 class="text-2xl font-black">{{ $mentor->name }}</h3>
                <p class="text-indigo-200 text-sm mt-1">{{ $mentor->expertise }}</p>
                <div class="text-3xl font-black mt-4">{{ $mentor->formatted_price }}</div>
            </div>

            <div class="px-6 py-5 space-y-4">
                {{-- Ringkasan sesi --}}
                <div class="bg-gray-50 rounded-xl p-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Tanggal</span>
                        <span class="font-semibold text-gray-800">{{ $schedule->date->format('d M Y') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Jam</span>
                        <span class="font-semibold text-gray-800">{{ substr($schedule->start_time,0,5) }} – {{ substr($schedule->end_time,0,5) }} WIB</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Durasi</span>
                        <span class="font-semibold text-gray-800">{{ $mentor->session_duration_minutes }} menit</span>
                    </div>
                    <div class="flex justify-between text-sm pt-2 border-t border-gray-200">
                        <span class="text-gray-500">Order ID</span>
                        <span class="font-mono text-xs text-gray-600">{{ $booking->order_id }}</span>
                    </div>
                </div>

                @if($booking->notes)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3">
                    <p class="text-xs font-semibold text-amber-700 mb-1">Catatan Anda:</p>
                    <p class="text-sm text-amber-800">{{ $booking->notes }}</p>
                </div>
                @endif

                {{-- Tombol bayar --}}
                <button id="pay-button"
                        class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl transition-colors flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Bayar Sekarang
                </button>

                {{-- Batalkan --}}
                <form method="POST" action="{{ route('mahasiswa.mentors.booking.cancel', $booking) }}">
                    @csrf @method('DELETE')
                    <button type="submit" onclick="return confirm('Batalkan booking ini?')"
                            class="w-full py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700 transition-colors">
                        Batalkan Booking
                    </button>
                </form>

                <p class="text-center text-xs text-gray-400">
                    Pembayaran aman via Midtrans.<br>
                    Transfer bank · GoPay · QRIS · Kartu kredit.
                </p>
            </div>
        </div>
    </div>

    <script src="{{ config('midtrans.snap_url') }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        // Tunggu sampai snap library benar-benar loaded
        window.addEventListener('load', function () {
            var btn = document.getElementById('pay-button');
            if (!btn) return;

            btn.addEventListener('click', function () {
                if (typeof snap === 'undefined') {
                    alert('Payment gateway sedang loading, coba lagi dalam beberapa detik.');
                    return;
                }
                snap.pay('{{ $snapToken }}', {
                    onSuccess: function(result) {
                        window.location.href = '{{ route('mahasiswa.mentors.my-bookings') }}';
                    },
                    onPending: function(result) {
                        window.location.href = '{{ route('mahasiswa.mentors.my-bookings') }}';
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal: ' + (result.status_message || 'Silakan coba lagi.'));
                    },
                    onClose: function() {
                        // User tutup popup tanpa bayar — tetap di halaman ini
                    }
                });
            });
        });
    </script>
</x-app-layout>
