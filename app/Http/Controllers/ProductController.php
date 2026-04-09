<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request) // <--- Add (Request $request)
    {
        // Need to get the current page number for the cache key
        $page = $request->get('page', 1);
        // 1. Get from cache (or store it if not cached)
        $products = Cache::remember("products.page.{$page}", now()->addMinutes(5), function () use ($page) {
            $paginated = Product::with('category')->paginate(15);
            return [
                'data' => $paginated->getCollection()->map(fn($p) => [
                    'id'       => $p->id,
                    'name'     => $p->name,
                    'price'    => $p->price,
                    'stock'    => $p->stock,
                    'category' => $p->category->name,
                ]),
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ];
        });
        // 2. Just return the cached data! 
        // Delete the old foreach loop and Product::all() call.
        return response()->json($products);
    }

    public function salesReport()
    {
        $orders = Order::with(['items.product', 'customer'])->paginate(15);

        $report = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $report[] = [
                    'order_id'     => $order->id,
                    'product_name' => $item->product->name,
                    'qty'          => $item->quantity,
                    'total'        => $item->quantity * $item->product->price,
                    'customer'     => $order->customer->name,
                ];
            }
        }

        return response()->json($report);
    }

    public function dashboard()
    {
        // 1. Just return the cached data directly!
        $data = Cache::remember('dashboard.stats', now()->addMinutes(10), function () {
            return [
                'total_products' => Product::count(), // Fast: SELECT COUNT(*)
                'total_orders'   => Order::count(),
                'total_revenue'  => Order::sum('total_amount'),
                'categories'     => Category::all(),
                'top_products'   => Product::orderByDesc('sold_count')->take(5)->get(), // Fast: ORDER BY + LIMIT
            ];
        });
        // 2. Delete all the old slow code (Product::all()->count()) below.
        return response()->json($data);
    }

    public function search(Request $request)
    {
        $keyword  = $request->input('q');
        $products = Product::where('name', 'LIKE', '%' . $keyword . '%')
                           ->orWhere('description', 'LIKE', '%' . $keyword . '%')
                           ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($request->all());

        // Invalidate first-page cache so fresh data shows immediately
        Cache::forget('products.page.1');
        Cache::forget('dashboard.stats');

        return response()->json($product, 201);
    }
}
