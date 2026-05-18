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
 * ReduceProductStockJob
 *
 * Job asinkron yang di-dispatch oleh OrderController saat order dibuat.
 * Mengirim HTTP request ke Product Service untuk mengurangi stok.
 *
 * Alur:
 * 1. OrderController::store() → dispatch(ReduceProductStockJob)
 * 2. Job masuk ke Redis Queue
 * 3. Queue Worker mengambil job dari Redis
 * 4. Job mengirim HTTP POST ke Product Service /api/products/{id}/reduce-stock
 * 5. Jika gagal, retry sampai 3x dengan interval 5 detik
 */
class ReduceProductStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika job gagal.
     */
    public int $tries = 3;

    /**
     * Waktu tunggu (detik) sebelum retry.
     */
    public int $backoff = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $productId,
        public int $quantity,
        public int $orderId
    ) {}

    /**
     * Execute the job.
     * Dipanggil oleh Queue Worker saat memproses job ini.
     */
    public function handle(): void
    {
        $productServiceUrl = env('PRODUCT_SERVICE_URL', 'http://nginx-product:80');

        Log::info("[ASYNC-QUEUE] ReduceProductStockJob: Mengurangi stok produk #{$this->productId} sebanyak {$this->quantity} untuk order #{$this->orderId}");

        $response = Http::timeout(10)->post(
            "{$productServiceUrl}/api/products/{$this->productId}/reduce-stock",
            ['quantity' => $this->quantity]
        );

        if ($response->successful()) {
            Log::info("[ASYNC-QUEUE] ReduceProductStockJob: ✅ Stok berhasil dikurangi untuk produk #{$this->productId}");
        } else {
            Log::error("[ASYNC-QUEUE] ReduceProductStockJob: ❌ Gagal mengurangi stok. Response: " . $response->body());
            // Throw exception agar job di-retry
            throw new \Exception("Failed to reduce stock for product #{$this->productId}. HTTP {$response->status()}");
        }
    }

    /**
     * Handle a job failure setelah semua retry habis.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("[ASYNC-QUEUE] ReduceProductStockJob: 🚨 PERMANENTLY FAILED untuk order #{$this->orderId}, produk #{$this->productId}. Error: {$exception->getMessage()}");
    }
}
