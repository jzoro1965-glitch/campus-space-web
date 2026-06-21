<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Task Scheduling
|--------------------------------------------------------------------------
|
| Jalankan scheduler ini di server dengan cron job:
|   * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
|
| Untuk testing lokal tanpa cron:
|   php artisan schedule:work   (jalan terus tiap menit)
|   php artisan schedule:run    (jalankan sekali sekarang)
|
*/

// Expire booking otomatis setiap menit
// Grace period default 30 menit setelah end_time
Schedule::command('bookings:expire --grace=30')
    ->everyMinute()
    ->withoutOverlapping()   // skip jika run sebelumnya belum selesai
    ->runInBackground()      // tidak block proses lain
    ->appendOutputTo(storage_path('logs/expire-bookings.log'));
