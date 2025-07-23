<?php

namespace App\Listeners;

use Log;
use App\Events\OrderStatusUpdated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Services\FirebaseNotificationService;

class SendOrderNotification
{
    use InteractsWithQueue;

    protected $firebaseNotificationService;

    public function __construct(FirebaseNotificationService $firebaseNotificationService)
    {
        $this->firebaseNotificationService = $firebaseNotificationService;
    }

    public function handle(OrderStatusUpdated $event)
    {
        // Ambil token perangkat user (pastikan tidak null)
        $deviceToken = $event->order->user->device_token ?? null;
        // dd($deviceToken);
        if (!$deviceToken) {
            Log::info("User does not have a device token. Notification not sent.");
            return;
        }

        // Data notifikasi
        $title = "Status Pesanan Diperbarui";

        $status = $event->newStatus; // Status baru pesanan
        $body = $this->getCustomMessage($status);
        
        $data = [
            'order_id' => $event->order->id,
            'status' => $status,
        ];

        Log::info('DATA YANG DIKIRIM KE FIREBASE: ', [
            'tokens' => $deviceToken,
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);

        Log::info("MENJALANKAN HANDLE NOTIFIKASI UNTUK ORDER ID: " . $event->order->id);
        Log::info("DEVICE TOKEN: " . $deviceToken);
        Log::info("STATUS BARU: " . $event->newStatus);

        // Kirim notifikasi
        $this->firebaseNotificationService->sendNotification($deviceToken, $title, $body, $data);
    }

    private function getCustomMessage(string $status): string
    {
        switch ($status) {
            case 'PENDING':
                return "Silahkan lakukan proses pembayaran agar pesanan Anda bisa kami proses.";
            case 'ON_DELIVERY':
                return "Pesanan Anda sedang dalam perjalanan, silahkan tunggu.";
            case 'PACKED':
                return "Pesanan Anda telah dikemas dan akan segera dikirim.";
            case 'DELIVERED':
                return "Pesanan Anda telah sampai di tujuan. Mohon periksa sebelum diterima. Terimakasih!";
            case 'CANCELLED':
                return "Pesanan Anda telah dibatalkan. Jika ada pertanyaan, hubungi customer service.";
            case 'SUCCESS':
                return "Pembayaran Anda telah berhasil. Pesanan Anda sedang diproses.";
            default:
                return "Status pesanan Anda sekarang: " . ucfirst($status) . ".";
        }
    }
}
