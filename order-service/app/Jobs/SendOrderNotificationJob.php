<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * SendOrderNotificationJob
 *
 * Job asinkron untuk mengirim notifikasi setelah order dibuat.
 * Saat ini implementasi berupa log, tetapi bisa dikembangkan
 * menjadi email, push notification, atau webhook.
 *
 * Menunjukkan penggunaan Queue untuk proses yang tidak perlu
 * ditunggu oleh user (fire-and-forget).
 */
class SendOrderNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $orderId,
        public string $userName,
        public string $productName,
        public float $totalPrice
    ) {}

    public function handle(): void
    {
        // Simulasi pengiriman notifikasi (log-based)
        Log::info("[ASYNC-QUEUE] SendOrderNotificationJob: 📧 Mempersiapkan Notifikasi Order");
        Log::info("[ASYNC-QUEUE]   Order ID    : #{$this->orderId}");
        Log::info("[ASYNC-QUEUE]   User        : {$this->userName}");

        $notificationServiceUrl = env('NOTIFICATION_SERVICE_URL', 'http://nginx-notification:80');

        $message = "Order #{$this->orderId} berhasil dibuat untuk {$this->productName} seharga Rp " . number_format($this->totalPrice, 0, ',', '.');

        try {
            $response = Http::acceptJson()->post("{$notificationServiceUrl}/api/notifications", [
                'order_id'  => $this->orderId,
                'recipient' => $this->userName,
                'type'      => 'order_created',
                'title'     => 'Order Berhasil Dibuat',
                'message'   => $message,
            ]);

            if ($response->successful()) {
                Log::info("[ASYNC-QUEUE]   Status      : ✅ Notifikasi sukses dicatat di Notification Service");
            } else {
                Log::warning("[ASYNC-QUEUE]   Status      : ⚠️ Notification Service merespons dengan error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("[ASYNC-QUEUE]   Status      : 🚨 Gagal menghubungi Notification Service. Error: " . $e->getMessage());
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[ASYNC-QUEUE] SendOrderNotificationJob: 🚨 Gagal mengirim notifikasi untuk order #{$this->orderId}. Error: {$exception->getMessage()}");
    }
}
