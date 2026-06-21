<?php

namespace App\Http\Controllers;

use App\Models\MentorBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config as MidtransConfig;
use Midtrans\Notification;

class MidtransController extends Controller
{
    /**
     * POST /payment/notification
     *
     * Webhook server-to-server dari Midtrans.
     * Dipanggil otomatis setiap kali status transaksi berubah.
     * Route ini HARUS di-exclude dari CSRF middleware.
     *
     * Flow:
     * 1. Midtrans kirim POST ke endpoint ini
     * 2. Verifikasi signature key
     * 3. Cari booking berdasarkan order_id
     * 4. Update status booking
     * 5. Kalau paid → konfirmasi sesi (slot tetap terkunci)
     * 6. Kalau failed/expire → bebaskan slot jadwal kembali
     */
    public function notification(Request $request)
    {
        MidtransConfig::$serverKey    = config('midtrans.server_key');
        MidtransConfig::$isProduction = config('midtrans.is_production');

        try {
            $notif = new Notification();

            $orderId           = $notif->order_id;
            $transactionStatus = $notif->transaction_status;
            $fraudStatus       = $notif->fraud_status ?? null;
            $paymentType       = $notif->payment_type ?? null;
            $transactionId     = $notif->transaction_id ?? null;

            // Cari booking berdasarkan order_id
            $booking = MentorBooking::with('schedule')
                ->where('order_id', $orderId)
                ->first();

            if (! $booking) {
                Log::warning("[Midtrans] Order tidak ditemukan: {$orderId}");
                return response()->json(['message' => 'Order not found'], 404);
            }

            // Simpan raw response untuk audit/debug
            $booking->update([
                'midtrans_transaction_id' => $transactionId,
                'payment_type'            => $paymentType,
                'midtrans_response'       => $request->all(),
            ]);

            // Tentukan status baru berdasarkan notifikasi Midtrans
            if ($transactionStatus === 'capture') {
                $newStatus = ($fraudStatus === 'challenge') ? 'pending' : 'paid';
            } elseif ($transactionStatus === 'settlement') {
                $newStatus = 'paid';
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'failure'])) {
                $newStatus = 'failed';
            } elseif ($transactionStatus === 'expire') {
                $newStatus = 'expired';
            } elseif ($transactionStatus === 'refund') {
                $newStatus = 'cancelled';
            } else {
                $newStatus = 'pending';
            }

            $booking->update(['status' => $newStatus]);

            if ($newStatus === 'paid' && $booking->paid_at === null) {
                // Pembayaran sukses → catat waktu bayar, slot tetap terkunci
                $booking->update(['paid_at' => now()]);

                Log::info("[Midtrans] PAID — Order {$orderId} | Mentor: {$booking->mentor_id} | User: {$booking->user_id}");
            }

            if (in_array($newStatus, ['failed', 'expired', 'cancelled'])) {
                // Pembayaran gagal → bebaskan slot jadwal kembali
                if ($booking->schedule) {
                    $booking->schedule->update(['is_booked' => false]);
                }
                Log::info("[Midtrans] {$newStatus} — Order {$orderId}, slot dibebaskan.");
            }

            return response()->json(['message' => 'OK']);

        } catch (\Exception $e) {
            Log::error('[Midtrans] Notification error: ' . $e->getMessage());
            return response()->json(['message' => 'Error'], 500);
        }
    }
}
