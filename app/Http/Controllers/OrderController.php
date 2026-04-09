<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items'       => 'required|array',
        ]);

        $totalAmount = 0;

        $order = Order::create([
            'customer_id'  => $request->customer_id,
            'total_amount' => 0,
            'status'       => 'pending',
        ]);

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);

            if (!$product || $product->stock < $item['quantity']) {
                return response()->json(['error' => 'Product unavailable'], 422);
            }

            OrderItem::create([
                'order_id'   => $order->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $product->price,
            ]);

            $product->decrement('stock', $item['quantity']);

            $totalAmount += $product->price * $item['quantity'];
        }

        $order->update(['total_amount' => $totalAmount]);

        return response()->json($order, 201);
    }

    public function index()
    {
        $orders = Order::with('customer','items')->paginate(15);
        $data = $orders->getCollection()->map(fn($order) => [
            'id'          => $order->id,
            'customer'    => $order->customer->name,
            'total'       => $order->total_amount,
            'status'      => $order->status,
            'items_count' => $order->items->count(),
            'created_at'  => $order->created_at,
        ]);
        // ✅ FIX #3: Return pagination metadata
        return response()->json([
            'data'         => $data,
            'current_page' => $orders->currentPage(),
            'total'        => $orders->total(),
            'last_page'    => $orders->lastPage(),
        ]);
    }

    public function filterByStatus(Request $request)
    {
        $status = $request->input('status');
        // ✅ FIX #3 & #6: Use Eloquent and paginate(15)
        // This also fixes the SQL Injection risk
        $orders = Order::where('status', $status)
                    ->with(['customer', 'items'])
                    ->paginate(15);
        return response()->json($orders);
    }
}
