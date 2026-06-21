<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    | Isi nilai di file .env:
    |   MIDTRANS_SERVER_KEY=SB-Mid-server-...
    |   MIDTRANS_CLIENT_KEY=SB-Mid-client-...
    |   MIDTRANS_IS_PRODUCTION=false
    |
    | Untuk production ganti MIDTRANS_IS_PRODUCTION=true dan ganti key ke
    | key production dari dashboard.midtrans.com
    */

    'server_key'   => env('MIDTRANS_SERVER_KEY', ''),
    'client_key'   => env('MIDTRANS_CLIENT_KEY', ''),
    'is_production'=> env('MIDTRANS_IS_PRODUCTION', false),
    'snap_url'     => env('MIDTRANS_SNAP_URL', 'https://app.sandbox.midtrans.com/snap/snap.js'),

    // Webhook dari Midtrans — harus bisa diakses dari internet
    // Untuk lokal: pakai ngrok → MIDTRANS_NOTIFICATION_URL=https://xxxx.ngrok.io/payment/notification
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL', env('APP_URL') . '/payment/notification'),

    // Redirect setelah popup Midtrans ditutup (finish/error/pending)
    'finish_url'  => env('APP_URL') . '/mahasiswa/mentors/bookings',
    'error_url'   => env('APP_URL') . '/mahasiswa/mentors/bookings',
    'pending_url' => env('APP_URL') . '/mahasiswa/mentors/bookings',
];
