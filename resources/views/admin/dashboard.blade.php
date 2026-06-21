<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Dashboard Monitoring</h2>
                <p class="text-xs text-gray-400 mt-0.5">{{ now()->translatedFormat('l, d F Y') }} — Data diperbarui otomatis setiap 30 detik</p>
            </div>
            <span class="px-3 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-full flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Sistem Live
            </span>
        </div>
    </x-slot>

    {{-- Auto-refresh setiap 30 detik --}}
    <script>setTimeout(() => location.reload(), 30000);</script>

    <div class="space-y-6">

        {{-- ── KARTU STATISTIK ──────────────────────────────────────── --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Meja</p>
                <h3 class="text-3xl font-extrabold text-gray-800 mt-1">{{ $desks->count() }}</h3>
                <p class="text-xs text-blue-500 mt-1">Semua lantai</p>
            </div>

            <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Booking Hari Ini</p>
                <h3 class="text-3xl font-extrabold text-red-600 mt-1">{{ $activeBookings->count() }}</h3>
                <p class="text-xs text-red-400 mt-1">Meja terpakai</p>
            </div>

            <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Meja Tersedia</p>
                <h3 class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $desks->count() - $activeBookings->count() }}</h3>
                <p class="text-xs text-emerald-400 mt-1">Siap digunakan</p>
            </div>

            <div class="p-5 bg-white rounded-2xl border border-gray-100 shadow-sm">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Mahasiswa</p>
                <h3 class="text-3xl font-extrabold text-indigo-600 mt-1">{{ $totalMahasiswa }}</h3>
                <p class="text-xs text-indigo-400 mt-1">{{ $totalBookingBulanIni }} booking bulan ini</p>
            </div>
        </div>

        {{-- ── GRAFIK + TOP MEJA ─────────────────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Grafik 7 hari --}}
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Booking 7 Hari Terakhir</h3>
                <canvas id="weeklyChart" height="100"></canvas>
            </div>

            {{-- Top 5 Meja --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <h3 class="text-base font-bold text-gray-800 mb-4">Meja Terpopuler</h3>
                @forelse($topDesks as $i => $item)
                    <div class="flex items-center justify-between py-2 {{ $i < $topDesks->count()-1 ? 'border-b border-gray-50' : '' }}">
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 rounded-full bg-indigo-100 text-indigo-700 text-xs font-bold flex items-center justify-center">{{ $i+1 }}</span>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $item->desk->code ?? '-' }}</p>
                                <p class="text-xs text-gray-400">{{ $item->desk->location ?? '' }}</p>
                            </div>
                        </div>
                        <span class="text-sm font-bold text-indigo-600">{{ $item->total }}x</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada data.</p>
                @endforelse
            </div>
        </div>

        {{-- ── DENAH VISUAL MEJA ─────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-bold text-gray-800">Denah Visual Meja — Hari Ini</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Real-time · auto-refresh 30 detik</p>
                </div>
                <div class="flex items-center gap-4 text-xs font-medium">
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-emerald-500"></span> Kosong</span>
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-red-500"></span> Ter-booking</span>
                    <span class="flex items-center gap-1.5 text-gray-600"><span class="w-3 h-3 rounded bg-gray-400"></span> Nonaktif</span>
                </div>
            </div>

            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4">
                @foreach($desks as $desk)
                    @php $isBooked = $activeBookings->contains('desk_id', $desk->id); @endphp
                    <div class="p-4 rounded-2xl border transition-all duration-300 hover:-translate-y-1 hover:shadow-md
                        {{ !$desk->is_active
                            ? 'bg-gray-50 border-gray-200 text-gray-400 opacity-50'
                            : ($isBooked ? 'bg-red-50 border-red-200 text-red-900' : 'bg-emerald-50 border-emerald-200 text-emerald-900') }}">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-[10px] uppercase font-semibold text-gray-400">{{ $desk->location }}</span>
                            @if($desk->is_active)
                                <span class="flex h-2 w-2 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $isBooked ? 'bg-red-400' : 'bg-emerald-400' }}"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 {{ $isBooked ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                </span>
                            @else
                                <span class="flex h-2 w-2 relative">
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-gray-400"></span>
                                </span>
                            @endif
                        </div>
                        <div class="text-xl font-black">{{ $desk->code }}</div>
                        <div class="mt-1.5 text-[10px] font-bold uppercase px-2 py-0.5 rounded-full inline-block
                            {{ !$desk->is_active
                                ? 'bg-gray-200 text-gray-500'
                                : ($isBooked ? 'bg-red-200/60 text-red-800' : 'bg-emerald-200/60 text-emerald-800') }}">
                            {{ !$desk->is_active ? 'Nonaktif' : ($isBooked ? 'Ter-Booking' : 'Kosong') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── LOG BOOKING HARI INI ──────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-gray-800">Log Booking Aktif Hari Ini</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $activeBookings->count() }} booking aktif</p>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="text-xs text-indigo-600 font-semibold hover:underline">
                    Lihat semua →
                </a>
            </div>
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead class="bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-left">#</th>
                        <th class="px-6 py-3 text-left">Mahasiswa</th>
                        <th class="px-6 py-3 text-left">NIM</th>
                        <th class="px-6 py-3 text-left">Meja</th>
                        <th class="px-6 py-3 text-left">Sesi Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($activeBookings as $i => $booking)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3 text-gray-400 font-mono text-xs">{{ $i+1 }}</td>
                            <td class="px-6 py-3">
                                <div class="font-semibold text-gray-900">{{ $booking->user->name ?? '-' }}</div>
                                <div class="text-xs text-gray-400">{{ $booking->user->email ?? '' }}</div>
                            </td>
                            <td class="px-6 py-3 font-mono text-gray-500 text-xs">{{ $booking->user->nim ?? '-' }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-lg text-xs font-bold">
                                    {{ $booking->desk->code ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 font-mono text-xs text-gray-700">
                                {{ substr($booking->start_time, 0, 5) }} – {{ substr($booking->end_time, 0, 5) }} WIB
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-sm">
                                Belum ada booking aktif hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- Chart.js via CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const ctx = document.getElementById('weeklyChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($chartLabels) !!},
                datasets: [{
                    label: 'Jumlah Booking',
                    data: {!! json_encode($chartData) !!},
                    backgroundColor: 'rgba(99, 102, 241, 0.7)',
                    borderColor: 'rgba(99, 102, 241, 1)',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    </script>
</x-app-layout>
