<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireBookings extends Command
{
    /**
     * Nama command yang dipanggil via artisan.
     * Contoh: php artisan bookings:expire
     */
    protected $signature = 'bookings:expire
                            {--grace=30 : Menit toleransi setelah end_time sebelum booking di-expire}
                            {--dry-run  : Tampilkan daftar booking yang akan di-expire tanpa benar-benar mengubah data}';

    protected $description = 'Otomatis mengubah status booking menjadi expired jika sesi sudah lewat dari waktu selesai (+ grace period).';

    public function handle(): int
    {
        $graceMinutes = (int) $this->option('grace');
        $isDryRun     = $this->option('dry-run');
        $now          = Carbon::now();

        // Ambil semua booking approved yang sudah lewat:
        // (booking_date + end_time + grace period) < sekarang
        $expired = Booking::where('status', 'approved')
            ->where(function ($query) use ($now, $graceMinutes) {
                // Booking di hari sebelumnya — pasti sudah lewat
                $query->where('booking_date', '<', $now->toDateString())
                    // Atau booking hari ini yang end_time-nya sudah lewat + grace period
                    ->orWhere(function ($q) use ($now, $graceMinutes) {
                        $q->where('booking_date', $now->toDateString())
                          ->whereRaw(
                              "ADDTIME(booking_date, end_time) < ?",
                              [$now->copy()->subMinutes($graceMinutes)->format('Y-m-d H:i:s')]
                          );
                    });
            })
            ->with('desk', 'user')
            ->get();

        if ($expired->isEmpty()) {
            $this->info('[' . $now->format('H:i:s') . '] Tidak ada booking yang perlu di-expire.');
            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn("[DRY RUN] {$expired->count()} booking akan di-expire:");
            $this->table(
                ['ID', 'Mahasiswa', 'Meja', 'Tanggal', 'Jam Selesai'],
                $expired->map(fn ($b) => [
                    $b->id,
                    $b->user->name ?? '—',
                    $b->desk->code ?? '—',
                    $b->booking_date,
                    substr($b->end_time, 0, 5),
                ])->toArray()
            );
            return self::SUCCESS;
        }

        // Update massal — lebih efisien dari loop satu-satu
        $ids = $expired->pluck('id');
        Booking::whereIn('id', $ids)->update(['status' => 'expired']);

        $this->info(
            '[' . $now->format('H:i:s') . '] ' .
            $expired->count() . ' booking di-expire ' .
            "(grace period: {$graceMinutes} menit)."
        );

        // Log detail ke output (berguna saat debugging)
        if ($this->getOutput()->isVerbose()) {
            $this->table(
                ['ID', 'Mahasiswa', 'Meja', 'Tanggal', 'Jam Selesai'],
                $expired->map(fn ($b) => [
                    $b->id,
                    $b->user->name ?? '—',
                    $b->desk->code ?? '—',
                    $b->booking_date,
                    substr($b->end_time, 0, 5),
                ])->toArray()
            );
        }

        return self::SUCCESS;
    }
}
