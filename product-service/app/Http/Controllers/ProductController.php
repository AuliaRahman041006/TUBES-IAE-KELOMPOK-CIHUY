<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::all());
    }

    public function store(Request $request)
    {
        if ($request->attributes->get('role') !== 'admin') {
            return response()->json([
                'message' => 'Forbidden. Hanya Admin yang dapat menambah produk.'
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
        ]);

        $product = Product::create($request->all());

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product
        ], 201);
    }

    public function decrementStock(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $totalAmount = 0;
        $productsToUpdate = [];

        // Validate all stocks first to prevent partial updates
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            
            if ($product->stock < $item['quantity']) {
                return response()->json([
                    'message' => "Stok produk '{$product->name}' tidak mencukupi. Tersedia: {$product->stock}"
                ], 400);
            }
            
            $totalAmount += ($product->price * $item['quantity']);
            $productsToUpdate[] = [
                'product' => $product,
                'quantity' => $item['quantity']
            ];
        }

        // Apply decrements
        foreach ($productsToUpdate as $data) {
            $data['product']->decrement('stock', $data['quantity']);
        }

        return response()->json([
            'message' => 'Stock decremented successfully',
            'total_amount' => $totalAmount
        ]);
    }
}
