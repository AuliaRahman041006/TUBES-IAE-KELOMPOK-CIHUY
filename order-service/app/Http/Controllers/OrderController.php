<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            // 'user_id' => 'required|integer', // Now handled by Bearer Token
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Call Product Service to decrement stock and get real total price
        try {
            $response = Http::timeout(15)->post('http://product_service:8000/api/products/decrement', [
                'items' => $request->items
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'Gagal memproses pesanan.',
                    'error' => $response->json('message') ?? 'Terjadi kesalahan pada Product Service.'
                ], $response->status());
            }

            $totalAmount = $response->json('total_amount');
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Product Service tidak dapat dihubungi.',
                'error' => $e->getMessage()
            ], 503);
        }

        $order = Order::create([
            'user_id' => $request->attributes->get('user_id'),
            'total_amount' => $totalAmount,
            'status' => 'success'
        ]);

        foreach ($request->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                // Because we don't return individual prices from decrement API in this simplified version,
                // we leave price as 0 or derive it if needed. Let's just set it to 0 for now since total_amount is accurate.
                'price' => 0 
            ]);
        }

        // Publish event to Redis for Notification Service
        $eventData = json_encode([
            'user_id' => $order->user_id,
            'order_id' => $order->id,
            'message' => "Pesanan #{$order->id} berhasil dibuat dengan total Rp" . number_format($totalAmount, 0, ',', '.')
        ]);

        Redis::publish('order_created', $eventData);

        $totalQuantity = collect($request->items)->sum('quantity');

        return response()->json([
            'message' => 'Order created successfully and notification event published',
            'order' => [
                'user_id' => $order->user_id,
                'total_amount' => $order->total_amount,
                'quantity' => $totalQuantity,
                'status' => $order->status,
                'created_at' => $order->created_at,
                'order_id' => $order->id,
            ]
        ], 201);
    }
}
