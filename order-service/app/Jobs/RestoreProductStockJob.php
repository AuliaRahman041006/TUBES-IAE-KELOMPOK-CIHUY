<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * RestoreProductStockJob
 *
 * Job asinkron yang di-dispatch saat order dibatalkan (cancel).
 * Mengirim HTTP request ke Product Service untuk mengembalikan stok.
 *
 * Alur:
 * 1. OrderController::cancel() atau updateStatus('cancelled') → dispatch(RestoreProductStockJob)
 * 2. Job masuk ke Redis Queue
 * 3. Queue Worker mengambil job dari Redis
 * 4. Job mengirim HTTP POST ke Product Service /api/products/{id}/restore-stock
 */
class RestoreProductStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        public int $productId,
        public int $quantity,
        public int $orderId
    ) {}

    public function handle(): void
    {
        $productServiceUrl = env('PRODUCT_SERVICE_URL', 'http://nginx-product:80');

        Log::info("[ASYNC-QUEUE] RestoreProductStockJob: Mengembalikan stok produk #{$this->productId} sebanyak {$this->quantity} untuk order #{$this->orderId}");

        $response = Http::timeout(10)->post(
            "{$productServiceUrl}/api/products/{$this->productId}/restore-stock",
            ['quantity' => $this->quantity]
        );

        if ($response->successful()) {
            Log::info("[ASYNC-QUEUE] RestoreProductStockJob: ✅ Stok berhasil dikembalikan untuk produk #{$this->productId}");
        } else {
            Log::error("[ASYNC-QUEUE] RestoreProductStockJob: ❌ Gagal mengembalikan stok. Response: " . $response->body());
            throw new \Exception("Failed to restore stock for product #{$this->productId}. HTTP {$response->status()}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("[ASYNC-QUEUE] RestoreProductStockJob: 🚨 PERMANENTLY FAILED untuk order #{$this->orderId}, produk #{$this->productId}. Error: {$exception->getMessage()}");
    }
}
