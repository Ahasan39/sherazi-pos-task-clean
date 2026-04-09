<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'items'       => 'required|array',
        ]);

        // ✅ WRAP EVERYTHING IN TRANSACTION
        try {
            $order = DB::transaction(function () use ($request) {
                $totalAmount = 0;

                // 1. Create the order shell
                $order = Order::create([
                    'customer_id'  => $request->customer_id,
                    'total_amount' => 0,
                    'status'       => 'pending',
                ]);

                foreach ($request->items as $item) {
                    // ✅ USE lockForUpdate() to prevent two people 
                    // buying the same item at the same time
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if (!$product || $product->stock < $item['quantity']) {
                        // Aborting inside a transaction triggers automatic rollback
                        abort(422, "Product {$product?->name} is unavailable or out of stock.");
                    }

                    OrderItem::create([
                        'order_id'   => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity'   => $item['quantity'],
                        'unit_price' => $product->price,
                    ]);

                    $product->decrement('stock', $item['quantity']);
                    $totalAmount += $product->price * $item['quantity'];
                    
                    // Bonus: Track sold count
                    $product->increment('sold_count', $item['quantity']);
                }

                // 2. Finalize total
                $order->update(['total_amount' => $totalAmount]);

                // Clear statistics cache because new order created
                Cache::forget('dashboard.stats');

                return $order;
            });

            return response()->json($order, 201);

        } catch (\Exception $e) {
            // This is where aborted transactions end up
            return response()->json([
                'error'   => 'Transaction failed',
                'message' => $e->getMessage()
            ], 422);
        }
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
        // ✅ FIX #6 SECURITY: Only allow these 3 values
        $request->validate([
            'status' => 'required|in:pending,completed,cancelled',
        ]);

        $status = $request->input('status');

        $orders = Order::where('status', $status)
                    ->with(['customer', 'items'])
                    ->paginate(15);

        return response()->json($orders);
    }

}
