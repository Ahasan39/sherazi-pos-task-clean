# Sherazi IT — Laravel Performance Audit
**Position:** Senior Laravel Developer | **Task:** Performance Fix & Optimization

---

## কী Fix করলাম এবং কেন?
## What I Fixed & Why

---

### ১. N+1 Query Problem → Eager Loading দিয়ে Fix

**সমস্যা কী ছিল?**
`GET /api/products` endpoint এ প্রতিটি product এর জন্য আলাদাভাবে category fetch করা হচ্ছিল।
500 products থাকলে → **501টি SQL query** execute হতো।
Orders endpoint এ 200 orders → **401টি query** হতো।
Sales report এ nested loop → **1000+ queries** হতো।

**কীভাবে Fix করলাম?**
```php
// ❌ আগে — প্রতি row এ আলাদা query
Product::all(); // loop এ $product->category->name

// ✅ এখন — একটাই batch query সব relationship এর জন্য
Product::with('category')->paginate(15);
Order::with(['customer', 'items'])->paginate(15);
Order::with(['items.product', 'customer'])->paginate(15);
```

**ফলাফল:** 501 queries → **2 queries** (যতই record থাকুক)

---

### ২. Cache নেই → Cache::remember() দিয়ে Fix

**সমস্যা কী ছিল?**
Dashboard এ প্রতিটি request এ 5টি heavy DB query বার বার execute হতো।
Products list প্রতিবার সব row MySQL থেকে টেনে আনতো।

**কীভাবে Fix করলাম?**
```php
// ✅ Products — 5 মিনিট cache প্রতি page এর
$products = Cache::remember("products.page.{$page}", now()->addMinutes(5), fn() =>
    Product::with('category')->paginate(15)
);

// ✅ Dashboard — 10 মিনিট cache
$stats = Cache::remember('dashboard.stats', now()->addMinutes(10), fn() => [
    'total_products' => Product::count(),
    'total_revenue'  => Order::sum('total_amount'),
]);

// ✅ নতুন product/order তৈরিতে cache invalidate
Cache::forget('products.page.1');
Cache::forget('dashboard.stats');
```

**ফলাফল:** `/api/products` — 2300ms → **161ms** (14× দ্রুত)

---

### ৩. Pagination নেই → paginate(15) দিয়ে Fix

**সমস্যা কী ছিল?**
সব endpoints পুরো table একসাথে return করতো।
500 products → একটাই JSON response এ সব — memory waste, slow network।

**কীভাবে Fix করলাম?**
```php
// ❌ আগে
Product::all();

// ✅ এখন — প্রতি page এ 15টি record
Product::with('category')->paginate(15);
```

**ফলাফল:** Response এ `current_page`, `total`, `last_page` — frontend pagination ready।

---

### ৪. Database Index নেই → Migration দিয়ে Index যোগ

**সমস্যা কী ছিল?**
`products.name`, `products.sold_count`, `orders.status` — এই columns এ কোনো index ছিল না।
প্রতিটি search বা filter এ MySQL পুরো table scan করতো — O(n) complexity।

**কীভাবে Fix করলাম?**
```bash
php artisan make:migration add_indexes_to_tables
```
```php
$table->index('name', 'idx_products_name');
$table->index('sold_count', 'idx_products_sold_count');
$table->index('status', 'idx_orders_status');  // orders table
```

**ফলাফল:** O(n) full scan → **O(log n)** B-tree lookup।

---

### ৫. কোনো DB Transaction নেই → DB::transaction() দিয়ে Fix

**সমস্যা কী ছিল?**
Order create করার সময় — Order আগে save হতো, তারপর items loop।
যদি item #3 fail করে → item #1, #2 already committed, stock already decremented।
**Database এ corrupted partial data থেকে যেতো।**

**কীভাবে Fix করলাম?**
```php
// ✅ সব operation একসাথে atomic — যেকোনো error হলে সব rollback
$order = DB::transaction(function () use ($request) {
    $order = Order::create([...]);
    foreach ($request->items as $item) {
        // Race condition থেকে বাঁচাতে pessimistic lock
        $product = Product::lockForUpdate()->find($item['product_id']);
        if ($product->stock < $item['quantity']) abort(422);
        OrderItem::create([...]);
        $product->decrement('stock', $item['quantity']);
    }
    return $order;
});
```

**ফলাফল:** Atomic order creation — partial data corruption সম্পূর্ণ বন্ধ। Overselling prevented।

---

### ৬. SQL Injection Risk → Eloquent Parameterized Query দিয়ে Fix

**সমস্যা কী ছিল?**
```php
// ❌ Raw string interpolation — injection vulnerable!
DB::select("SELECT * FROM orders WHERE status = '$status'");
// Attacker পাঠিয়ে দিতে পারে: ?status=' OR '1'='1 → সব orders expose!
```

**কীভাবে Fix করলাম?**
```php
// ✅ Whitelist validation + Eloquent parameterized query (দুই স্তরে সুরক্ষা)
$request->validate(['status' => 'required|in:pending,completed,cancelled']);
Order::where('status', $request->input('status'))->paginate(15);
```

**ফলাফল:** SQL injection সম্পূর্ণ বন্ধ — input কখনো SQL string এ যায় না।

---

### ৭. Inefficient Aggregation → SQL Aggregate Functions দিয়ে Fix

**সমস্যা কী ছিল?**
```php
// ❌ পুরো table PHP memory তে load করে তারপর count/sort
Product::all()->count();           // 500 rows → PHP তে গণনা
Product::all()->sortByDesc(...);   // 500 rows → PHP তে sort, 5টা রাখো
```

**কীভাবে Fix করলাম?**
```php
// ✅ SQL এই করে নাও — PHP memory তে কিছু আসে না
Product::count();                             // SELECT COUNT(*)
Order::sum('total_amount');                   // SELECT SUM(...)
Product::orderByDesc('sold_count')->take(5);  // ORDER BY + LIMIT 5
```

**ফলাফল:** ~1MB memory usage → **< 1KB** (500 products count করতে)।

---

## 📊 Before vs After Summary

| Endpoint | আগে (queries) | এখন (queries) | Cached Time |
|---|---|---|---|
| `GET /api/products` | 501 queries | 2 queries | **161ms** |
| `GET /api/orders` | 401 queries | 3 queries | — |
| `GET /api/products/sales-report` | 1000+ queries | 2 queries | — |
| `GET /api/products/dashboard` | DB hit every time | 5 queries | **171ms** |
| `GET /api/orders/filter` | SQL Injection ⚠️ | Safe ✅ | — |

---

## 🛠 Setup Instructions

```bash
# 1. Install dependencies
composer install

# 2. Create database
mysql -u root -e "CREATE DATABASE sherazi_pos;"

# 3. Configure .env
cp .env.example .env
# Set: DB_CONNECTION=mysql, DB_DATABASE=sherazi_pos, DB_USERNAME=root
php artisan key:generate

# 4. Run migrations + seed
php artisan migrate:fresh --seed --force

# 5. Start server
php artisan serve
```

Visit: **http://127.0.0.1:8000**

---

## 🔗 API Endpoints

| Method | URL | Description |
|---|---|---|
| GET | `/api/products` | Paginated product list (cached) |
| GET | `/api/products/search?q=` | Search products |
| GET | `/api/products/dashboard` | Stats & top products (cached) |
| GET | `/api/products/sales-report` | Sales per order item |
| POST | `/api/products` | Create product |
| GET | `/api/orders` | Paginated order list |
| GET | `/api/orders/filter?status=` | Filter by status (safe) |
| POST | `/api/orders` | Create order (transactional) |

---

*Submitted for Sherazi IT — Senior Laravel Developer Interview Task | Deadline: 10-04-2026*
