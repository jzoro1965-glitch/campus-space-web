<?php

namespace App\Console\Commands;

use App\Models\MentorBooking;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ExpireMentorBookings extends Command
{
    /**
     * Nama command yang dipanggil via artisan.
     * Contoh: php artisan mentor-bookings:expire
     */
    protected $signature = 'mentor-bookings:expire
                            {--minutes= : Override menit toleransi sebelum booking pending di-expire (default: config midtrans.pending_expiry_minutes)}
                            {--dry-run  : Tampilkan daftar booking yang akan di-expire tanpa benar-benar mengubah data}';

    protected $description = 'Otomatis ubah status booking mentor menjadi "expired" jika masih pending (belum dibayar) melewati batas waktu, lalu bebaskan slot jadwalnya kembali.';

    public function handle(): int
    {
        $minutes  = (int) ($this->option('minutes') ?? config('midtrans.pending_expiry_minutes', 60));
        $isDryRun = $this->option('dry-run');
        $cutoff   = Carbon::now()->subMinutes($minutes);

        // Ambil semua booking mentor yang masih pending dan sudah dibuat
        // lebih lama dari batas toleransi (default 60 menit).
        $expired = MentorBooking::with(['schedule', 'user', 'mentor'])
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->get();

        if ($expired->isEmpty()) {
            $this->info('[' . Carbon::now()->format('H:i:s') . "] Tidak ada booking mentor pending yang perlu di-expire (>{$minutes} menit).");
            return self::SUCCESS;
        }

        if ($isDryRun) {
            $this->warn("[DRY RUN] {$expired->count()} booking mentor akan di-expire:");
            $this->table(
                ['ID', 'Order ID', 'Mahasiswa', 'Mentor', 'Dibuat'],
                $expired->map(fn ($b) => [
                    $b->id,
                    $b->order_id,
                    $b->user->name ?? '—',
                    $b->mentor->name ?? '—',
                    $b->created_at,
                ])->toArray()
            );
            return self::SUCCESS;
        }

        // Diproses satu per satu (bukan update massal) karena kita juga
        // perlu membebaskan slot jadwal terkait tiap booking.
        foreach ($expired as $booking) {
            $booking->status = 'expired';
            $booking->save();

            if ($booking->schedule) {
                $booking->schedule->update(['is_booked' => false]);
            }
        }

        $this->info(
            '[' . Carbon::now()->format('H:i:s') . '] ' .
            $expired->count() . ' booking mentor di-expire ' .
            "(pending >{$minutes} menit), slot dibebaskan kembali."
        );

        if ($this->getOutput()->isVerbose()) {
            $this->table(
                ['ID', 'Order ID', 'Mahasiswa', 'Mentor'],
                $expired->map(fn ($b) => [
                    $b->id, $b->order_id, $b->user->name ?? '—', $b->mentor->name ?? '—',
                ])->toArray()
            );
        }

        return self::SUCCESS;
    }
}