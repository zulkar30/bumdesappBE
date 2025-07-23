<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendOrderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $order;
    protected $newStatus;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order, $newStatus)
    {
        $this->order = $order;
        $this->newStatus = $newStatus;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(FirebaseNotificationService $firebaseNotificationService)
    {
        $deviceToken = $this->order->user->device_token ?? null;
        if (!$deviceToken) {
            Log::info("User does not have a device token.");
            return;
        }

        $title = "Status Pesanan Diperbarui";
        $body = match ($this->newStatus) {
            'PENDING' => "Silahkan lakukan pembayaran.",
            'ON_DELIVERY' => "Pesanan dalam perjalanan.",
            'PACKED' => "Pesanan dikemas.",
            'DELIVERED' => "Pesanan sudah sampai.",
            'CANCELLED' => "Pesanan dibatalkan.",
            'SUCCESS' => "Pembayaran sukses.",
            default => "Status: " . ucfirst($this->newStatus),
        };

        $data = [
            'order_id' => $this->order->id,
            'status' => $this->newStatus,
        ];

        Log::info("MENJALANKAN HANDLE NOTIFIKASI UNTUK ORDER ID: " . $this->order->id);
        Log::info("DEVICE TOKEN: " . $deviceToken);
        Log::info("STATUS BARU: " . $this->newStatus);

        $firebaseNotificationService->sendNotification($deviceToken, $title, $body, $data);
    }
}
