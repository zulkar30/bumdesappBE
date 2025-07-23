<?php

namespace App\Http\Controllers\API;

use Midtrans\Config;
use Midtrans\Notification;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Events\OrderStatusUpdated;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Services\FirebaseNotificationService;

class MidtransController extends Controller
{
    protected $firebaseNotificationService;

    public function __construct(FirebaseNotificationService $firebaseNotificationService)
    {
        $this->firebaseNotificationService = $firebaseNotificationService;
    }

    public function callback(Request $request)
    {
        // Konfigurasi Midtrans
        Config::$serverKey = config('services.midtrans.serverKey');
        Config::$isProduction = config('services.midtrans.isProduction');
        Config::$isSanitized = config('services.midtrans.isSanitized');
        Config::$is3ds = config('services.midtrans.is3ds');

        // Buat instance midtrans notification
        $notification = new Notification;

        // Assign variabel
        $status = $notification->transaction_status;
        $type = $notification->payment_type;
        $fraud = $notification->fraud_status;
        $order_id = $notification->order_id;
        $transaction_id_length = strlen((string) Transaction::max('id')); // Panjang maksimum ID transaksi di database
        $transaction_id = substr($order_id, 0, $transaction_id_length);

        $transaction = Transaction::findOrFail($transaction_id);

        // Handle notification
        if ($status == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    $transaction->status = 'PENDING';
                } else {
                    $transaction->status = 'SUCCESS';
                }
            }
        } elseif ($status == 'settlement') {
            $transaction->status = 'PACKED';
        } elseif ($status == 'pending') {
            $transaction->status = 'PENDING';
        } elseif ($status == 'deny') {
            $transaction->status = 'CANCELLED';
        } elseif ($status == 'expire') {
            $transaction->status = 'CANCELLED';
        } elseif ($status == 'cancel') {
            $transaction->status = 'CANCELLED';
        }

        // Simpan transaksi
        $transaction->save();

        $this->sendPushNotification($transaction);

        return ResponseFormatter::success($transaction->status, 'Pesan berhasil dikirim');
    }

    private function sendPushNotification($transaction)
    {
        $deviceToken = $transaction->user->device_token;
        $title = $transaction->user->name;
        $body = match ($transaction->status) {
            'PENDING' => "SELAMAT!\nPesanan Anda berhasil dipesan, silahkan selesaikan pembayaran agar segera diproses\nTerimakasih",
            'SUCCESS' => "SELAMAT\nPembayaran Anda berhasil, pesanan Anda akan segera kami proses\nTerimakasih",
            'PACKED' => "SELAMAT\nPembayaran Anda berhasil, pesanan Anda akan segera kami proses\nTerimakasih",
            'ON_DELIVERY' => "Pesanan Anda sedang dalam pengiriman\nMohon tunggu pihak pengantaran pesanan menghubungi Anda ketika sudah sampai tujuan\nTerimakasih",
            'DELIVERED' => "Pesanan Anda telah sampai, mohon di cek terlebih dahulu sebelum diterima\nTerimakasih",
            'CANCELLED' => "Pesanan Anda telah dibatalkan\nSilahkan cari produk yang Anda inginkan\nTerimakasih",
            default => "Status pesanan Anda telah diperbarui.",
        };
        $data = [
            'order_id' => $transaction->id
        ];

        if ($deviceToken) {
            $this->firebaseNotificationService->sendNotification($deviceToken, $title, $body, $data);
        }
    }

    public function success()
    {
        return view('midtrans.success');
    }

    public function unfinish()
    {
        return view('midtrans.unfinish');
    }

    public function error()
    {
        return view('midtrans.error');
    }
}
