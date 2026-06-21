<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('mahasiswa.mentors.my-bookings') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="text-xl font-bold text-gray-800">Detail Booking Mentor</h2>
        </div>
    </x-slot>

    <div class="max-w-lg mx-auto">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

            {{-- Status visual --}}
            @php $color = $booking->status_color; @endphp
            <div class="px-6 py-6 text-center border-b border-gray-100">
                @if(in_array($booking->status, ['paid','completed']))
                    <div class="w-16 h-16 mx-auto bg-emerald-100 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">
                        {{ $booking->status === 'completed' ? 'Sesi Selesai' : 'Booking Dikonfirmasi!' }}
                    </h3>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $booking->status === 'completed' ? 'Terima kasih telah menggunakan layanan mentoring.' : 'Pembayaran berhasil. Sampai jumpa di sesi Anda!' }}
                    </p>
                @elseif($booking->status === 'pending')
                    <div class="w-16 h-16 mx-auto bg-amber-100 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Menunggu Pembayaran</h3>
                    <p class="text-sm text-gray-500 mt-1">Selesaikan pembayaran untuk mengkonfirmasi sesi.</p>
                @else
                    <div class="w-16 h-16 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-3">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Booking {{ $booking->status_label }}</h3>
                @endif
                <span class="inline-block mt-3 px-3 py-1 rounded-full text-xs font-bold
                    bg-{{ $color }}-50 text-{{ $color }}-700 border border-{{ $color }}-200">
                    {{ strtoupper($booking->status_label) }}
                </span>
            </div>

            {{-- Detail --}}
            <div class="px-6 py-5 space-y-3">
                @php
                $rows = [
                    ['Mentor',        $booking->mentor->name ?? '-'],
                    ['Keahlian',      $booking->mentor->expertise ?? '-'],
                    ['Tanggal Sesi',  $booking->schedule ? $booking->schedule->date->format('d M Y') : '-'],
                    ['Jam Sesi',      $booking->schedule ? substr($booking->schedule->start_time,0,5).' – '.substr($booking->schedule->end_time,0,5).' WIB' : '-'],
                    ['Order ID',      $booking->order_id],
                    ['Nominal',       $booking->formatted_amount],
                    ['Metode Bayar',  $booking->payment_type ? ucfirst(str_replace('_',' ',$booking->payment_type)) : '—'],
                    ['Waktu Bayar',   $booking->paid_at?->format('d M Y H:i') ?? '—'],
                ];
                if ($booking->notes) $rows[] = ['Catatan', $booking->notes];
                @endphp
                @foreach($rows as [$label, $value])
                <div class="flex justify-between items-start py-2 border-b border-gray-50 last:border-0 gap-4">
                    <span class="text-sm text-gray-500 shrink-0">{{ $label }}</span>
                    <span class="text-sm font-semibold text-gray-800 text-right">{{ $value }}</span>
                </div>
                @endforeach
            </div>

            <div class="px-6 pb-5 flex gap-3">
                @if($booking->status === 'pending' && $booking->snap_token)
                    <button onclick="payNow('{{ $booking->snap_token }}')"
                            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors">
                        Bayar Sekarang
                    </button>
                @endif
                <a href="{{ route('mahasiswa.mentors.my-bookings') }}"
                   class="flex-1 text-center py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-colors">
                    Kembali
                </a>
            </div>
        </div>
    </div>

    @if($booking->status === 'pending' && $booking->snap_token)
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
