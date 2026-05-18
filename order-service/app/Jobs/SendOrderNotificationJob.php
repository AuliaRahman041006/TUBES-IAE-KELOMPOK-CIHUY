<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        Log::info("[ASYNC-QUEUE] SendOrderNotificationJob: 📧 Notifikasi Order");
        Log::info("[ASYNC-QUEUE]   Order ID    : #{$this->orderId}");
        Log::info("[ASYNC-QUEUE]   User        : {$this->userName}");
        Log::info("[ASYNC-QUEUE]   Produk      : {$this->productName}");
        Log::info("[ASYNC-QUEUE]   Total Harga : Rp " . number_format($this->totalPrice, 0, ',', '.'));
        Log::info("[ASYNC-QUEUE]   Status      : ✅ Notifikasi terkirim (async)");

        // Di production, bisa diganti dengan:
        // Mail::to($userEmail)->send(new OrderCreatedMail($this->orderId));
        // atau HTTP webhook, push notification, dll.
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[ASYNC-QUEUE] SendOrderNotificationJob: 🚨 Gagal mengirim notifikasi untuk order #{$this->orderId}. Error: {$exception->getMessage()}");
    }
}
