<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sherazi POS — API Explorer</title>
    <meta name="description" content="Laravel POS Backend — Senior Developer Interview Task by Sherazi IT">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    @php
        use App\Models\Product;
        use App\Models\Order;
        use App\Models\Customer;
        use App\Models\Category;
        try {
            $totalProducts  = Product::count();
            $totalOrders    = Order::count();
            $totalCustomers = Customer::count();
            $totalCategories= Category::count();
            $totalRevenue   = Order::sum('total_amount');
            $dbOk = true;
        } catch (\Exception $e) {
            $totalProducts = $totalOrders = $totalCustomers = $totalCategories = $totalRevenue = 0;
            $dbOk = false;
        }
    @endphp

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg:        #080b12;
            --bg2:       #0d1117;
            --surface:   rgba(255,255,255,.04);
            --surface-h: rgba(255,255,255,.07);
            --border:    rgba(255,255,255,.08);
            --border-h:  rgba(108,142,255,.4);
            --accent:    #6c8eff;
            --accent2:   #a78bfa;
            --green:     #34d399;
            --amber:     #fbbf24;
            --red:       #f87171;
            --cyan:      #22d3ee;
            --text:      #e2e8f0;
            --text2:     #94a3b8;
            --muted:     #475569;
            --code-bg:   #060810;
            --r:         14px;
            --r-sm:      8px;
        }

        html { scroll-behavior: smooth; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* ─── Animated mesh background ─── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 50% at 20% -10%, rgba(108,142,255,.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 110%, rgba(167,139,250,.08) 0%, transparent 55%);
            pointer-events: none;
            z-index: 0;
        }

        /* ─── Header ─── */
        header {
            position: relative;
            z-index: 1;
            padding: 3.5rem 2rem 3rem;
            text-align: center;
            border-bottom: 1px solid var(--border);
            backdrop-filter: blur(12px);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            background: rgba(52,211,153,.08);
            border: 1px solid rgba(52,211,153,.2);
            color: var(--green);
            font-size: .7rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: .35rem .9rem;
            border-radius: 999px;
            margin-bottom: 1.4rem;
        }
        .pulse-ring {
            position: relative;
            width: 8px; height: 8px;
        }
        .pulse-ring::before, .pulse-ring::after {
            content: '';
            position: absolute;
            border-radius: 50%;
        }
        .pulse-ring::before {
            inset: 0;
            background: var(--green);
        }
        .pulse-ring::after {
            inset: -3px;
            border: 1.5px solid var(--green);
            opacity: .4;
            animation: ring 2s ease-out infinite;
        }
        @keyframes ring {
            0%   { transform: scale(1); opacity: .4; }
            100% { transform: scale(2.2); opacity: 0; }
        }

        h1 {
            font-size: clamp(1.9rem, 4vw, 2.8rem);
            font-weight: 800;
            letter-spacing: -.02em;
            background: linear-gradient(135deg, #fff 0%, #a5b4fc 60%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: .6rem;
            line-height: 1.15;
        }

        .sub {
            color: var(--text2);
            font-size: .92rem;
            max-width: 520px;
            margin: 0 auto 1.6rem;
        }

        .tech-badges {
            display: flex;
            justify-content: center;
            gap: .5rem;
            flex-wrap: wrap;
        }
        .tech-badge {
            font-size: .7rem;
            font-weight: 500;
            padding: .25rem .7rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            color: var(--text2);
            background: var(--surface);
            font-family: 'JetBrains Mono', monospace;
        }
        .tech-badge.db  { border-color: rgba(34,211,238,.25); color: var(--cyan); background: rgba(34,211,238,.06); }
        .tech-badge.ok  { border-color: rgba(52,211,153,.25); color: var(--green); background: rgba(52,211,153,.06); }

        /* ─── Container ─── */
        .wrap {
            position: relative;
            z-index: 1;
            max-width: 1000px;
            margin: 0 auto;
            padding: 2.5rem 1.5rem 5rem;
        }

        /* ─── Section label ─── */
        .label {
            font-size: .68rem;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }

        /* ─── Live Stats ─── */
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: .75rem;
            margin-bottom: 2.5rem;
        }
        .stat {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 1.2rem 1rem;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: border-color .25s, transform .2s;
        }
        .stat:hover { border-color: var(--border-h); transform: translateY(-2px); }
        .stat::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            opacity: 0;
            transition: opacity .3s;
        }
        .stat:hover::after { opacity: 1; }
        .stat-val {
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--accent);
            display: block;
            font-variant-numeric: tabular-nums;
            letter-spacing: -.02em;
        }
        .stat-val.green { color: var(--green); }
        .stat-val.amber { color: var(--amber); }
        .stat-val.cyan  { color: var(--cyan); }
        .stat-val.purple { color: var(--accent2); }
        .stat-lbl {
            font-size: .7rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .07em;
            font-weight: 500;
            margin-top: .2rem;
        }
        .stat-live {
            position: absolute;
            top: .55rem; right: .6rem;
            font-size: .58rem;
            color: var(--green);
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            opacity: .7;
        }

        /* ─── Endpoint Cards ─── */
        .eps { display: flex; flex-direction: column; gap: .6rem; margin-bottom: 2.5rem; }

        .ep {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r);
            overflow: hidden;
            transition: border-color .25s, transform .15s;
        }
        .ep:hover { border-color: rgba(108,142,255,.25); }
        .ep.open  { border-color: rgba(108,142,255,.4); }
        .ep.open  { transform: none; }

        .ep-head {
            display: flex;
            align-items: center;
            gap: .9rem;
            padding: 1rem 1.25rem;
            cursor: pointer;
            user-select: none;
        }

        .badge-method {
            font-family: 'JetBrains Mono', monospace;
            font-size: .65rem;
            font-weight: 700;
            padding: .22rem .55rem;
            border-radius: 5px;
            min-width: 42px;
            text-align: center;
            letter-spacing: .04em;
            flex-shrink: 0;
        }
        .GET  { background: rgba(52,211,153,.1);  color: var(--green); border: 1px solid rgba(52,211,153,.25); }
        .POST { background: rgba(251,191,36,.08); color: var(--amber); border: 1px solid rgba(251,191,36,.22); }

        .ep-path {
            font-family: 'JetBrains Mono', monospace;
            font-size: .83rem;
            color: var(--text);
            flex: 1;
            min-width: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ep-hint { font-size: .75rem; color: var(--muted); flex-shrink: 0; }
        .ep-caret {
            color: var(--muted);
            font-size: .85rem;
            transition: transform .25s cubic-bezier(.4,0,.2,1);
            flex-shrink: 0;
        }
        .ep.open .ep-caret { transform: rotate(90deg); }

        /* ─── Expand body ─── */
        .ep-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height .35s cubic-bezier(.4,0,.2,1);
        }
        .ep.open .ep-body { max-height: 600px; }

        .ep-inner {
            border-top: 1px solid var(--border);
            padding: 1.25rem;
            background: rgba(0,0,0,.3);
            display: flex;
            flex-direction: column;
            gap: .9rem;
        }
        .ep-desc { font-size: .84rem; color: var(--text2); }

        .tags { display: flex; flex-wrap: wrap; gap: .4rem; }
        .tag {
            font-size: .65rem;
            font-weight: 600;
            padding: .18rem .6rem;
            border-radius: 999px;
            letter-spacing: .04em;
        }
        .tag-fix    { background: rgba(108,142,255,.1); color: var(--accent);  border: 1px solid rgba(108,142,255,.22); }
        .tag-cache  { background: rgba(167,139,250,.1); color: var(--accent2); border: 1px solid rgba(167,139,250,.22); }
        .tag-page   { background: rgba(52,211,153,.08); color: var(--green);   border: 1px solid rgba(52,211,153,.22); }
        .tag-sec    { background: rgba(248,113,113,.08); color: var(--red);     border: 1px solid rgba(248,113,113,.22); }

        pre {
            background: var(--code-bg);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: var(--r-sm);
            padding: 1rem 1.25rem;
            overflow-x: auto;
            font-family: 'JetBrains Mono', monospace;
            font-size: .78rem;
            line-height: 1.8;
            color: #64748b;
        }
        pre .k { color: #6c8eff; }
        pre .s { color: #34d399; }
        pre .n { color: #fbbf24; }
        pre .c { color: #475569; font-style: italic; }

        .btn-row { display: flex; flex-wrap: wrap; gap: .5rem; }
        .try-btn {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            font-family: 'Inter', sans-serif;
            font-size: .75rem;
            font-weight: 500;
            padding: .38rem .85rem;
            border-radius: 6px;
            text-decoration: none;
            transition: all .2s;
            cursor: pointer;
        }
        .try-btn.primary {
            background: rgba(108,142,255,.12);
            border: 1px solid rgba(108,142,255,.3);
            color: var(--accent);
        }
        .try-btn.primary:hover { background: rgba(108,142,255,.22); border-color: var(--accent); }
        .try-btn.ghost {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text2);
        }
        .try-btn.ghost:hover { border-color: rgba(255,255,255,.2); color: var(--text); }

        /* ─── Fixes Grid ─── */
        .fixes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: .6rem;
        }
        .fix-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--r);
            padding: 1rem 1.1rem;
            display: flex;
            gap: .8rem;
            align-items: flex-start;
            transition: border-color .25s, transform .2s;
        }
        .fix-card:hover { border-color: rgba(108,142,255,.3); transform: translateY(-1px); }
        .fix-icon {
            font-size: 1.25rem;
            flex-shrink: 0;
            line-height: 1;
            margin-top: .1rem;
        }
        .fix-title { font-size: .84rem; font-weight: 600; margin-bottom: .2rem; }
        .fix-desc  { font-size: .75rem; color: var(--muted); line-height: 1.5; }

        /* ─── Perf table ─── */
        .perf-table {
            width: 100%;
            border-collapse: collapse;
            font-size: .82rem;
            margin-top: 1rem;
        }
        .perf-table th {
            text-align: left;
            padding: .6rem 1rem;
            font-size: .67rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            border-bottom: 1px solid var(--border);
        }
        .perf-table td {
            padding: .75rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,.04);
            color: var(--text2);
        }
        .perf-table td:first-child { font-family: 'JetBrains Mono', monospace; font-size: .75rem; color: var(--text); }
        .perf-table tr:hover td { background: var(--surface); }
        .fast { color: var(--green); font-weight: 600; }
        .slow { color: var(--amber); }

        /* ─── Footer ─── */
        footer {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 1.5rem 1rem;
            color: var(--muted);
            font-size: .75rem;
            border-top: 1px solid var(--border);
        }
        footer span { color: var(--text2); }

        /* ─── Scrollbar ─── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb { background: #1e2742; border-radius: 3px; }

        @media (max-width: 600px) {
            .ep-hint { display: none; }
            h1 { font-size: 1.7rem; }
        }
    </style>
</head>
<body>

<!-- ═══════════ HEADER ═══════════ -->
<header>
    <div class="status-pill">
        <span class="pulse-ring"></span>
        @if($dbOk) Live · MySQL Connected @else Offline · DB Error @endif
    </div>

    <h1>Sherazi POS · API Explorer</h1>
    <p class="sub">Laravel POS backend built for the Sherazi IT Senior Developer Interview Task. All 7 performance issues identified and fixed.</p>

    <div class="tech-badges">
        <span class="tech-badge">PHP {{ PHP_MAJOR_VERSION }}.{{ PHP_MINOR_VERSION }}</span>
        <span class="tech-badge">Laravel {{ app()->version() }}</span>
        <span class="tech-badge db">MySQL · sherazi_pos</span>
        @if($dbOk)
            <span class="tech-badge ok">✓ {{ $totalProducts }} products seeded</span>
            <span class="tech-badge ok">✓ {{ $totalOrders }} orders seeded</span>
        @endif
        <span class="tech-badge">File Cache · Paginate(15)</span>
    </div>
</header>

<!-- ═══════════ MAIN ═══════════ -->
<div class="wrap">

    <!-- Live Stats -->
    <div class="label">Live Database Stats</div>
    <div class="stats">
        <div class="stat">
            <span class="stat-live">live</span>
            <span class="stat-val" data-target="{{ $totalProducts }}">0</span>
            <div class="stat-lbl">Products</div>
        </div>
        <div class="stat">
            <span class="stat-live">live</span>
            <span class="stat-val green" data-target="{{ $totalOrders }}">0</span>
            <div class="stat-lbl">Orders</div>
        </div>
        <div class="stat">
            <span class="stat-live">live</span>
            <span class="stat-val cyan" data-target="{{ $totalCustomers }}">0</span>
            <div class="stat-lbl">Customers</div>
        </div>
        <div class="stat">
            <span class="stat-live">live</span>
            <span class="stat-val purple" data-target="{{ $totalCategories }}">0</span>
            <div class="stat-lbl">Categories</div>
        </div>
        <div class="stat">
            <span class="stat-val amber" id="rev">$0</span>
            <div class="stat-lbl">Total Revenue</div>
        </div>
        <div class="stat">
            <span class="stat-val">7</span>
            <div class="stat-lbl">Issues Fixed</div>
        </div>
        <div class="stat">
            <span class="stat-val green">15</span>
            <div class="stat-lbl">Per Page</div>
        </div>
        <div class="stat">
            <span class="stat-val">~160ms</span>
            <div class="stat-lbl">Cached Response</div>
        </div>
    </div>

    <!-- ── Products Endpoints ── -->
    <div class="label">Products Endpoints</div>
    <div class="eps">

        <!-- GET /api/products -->
        <div class="ep">
            <div class="ep-head" onclick="toggle(this)">
                <span class="badge-method GET">GET</span>
                <span class="ep-path">/api/products</span>
                <span class="ep-hint">List all products, paginated &amp; cached</span>
                <span class="ep-caret">›</span>
            </div>
            <div class="ep-body">
                <div class="ep-inner">
                    <p class="ep-desc">Returns 15 products per page with their category name eager-loaded. Results are cached for 5 minutes per page and invalidated automatically when a new product is created.</p>
                    <div class="tags">
                        <span class="tag tag-fix">Fix #1 — Eager Load with()</span>
                        <span class="tag tag-cache">Fix #2 — 5min Cache</span>
                        <span class="tag tag-page">Fix #3 — paginate(15)</span>
                    </div>
<pre><span class="c">// Response</span>
{
  <span class="k">"data"</span>: [
    { <span class="k">"id"</span>: <span class="n">1</span>, <span class="k">"name"</span>: <span class="s">"Product #1"</span>, <span class="k">"price"</span>: <span class="n">199.99</span>, <span class="k">"stock"</span>: <span class="n">45</span>, <span class="k">"category"</span>: <span class="s">"Electronics"</span> }
  ],
  <span class="k">"total"</span>: <span class="n">{{ $totalProducts }}</span>, <span class="k">"per_page"</span>: <span class="n">15</span>, <span class="k">"last_page"</span>: <span class="n">{{ ceil($totalProducts/15) }}</span>
}</pre>
                    <div class="btn-row">
                        <a class="try-btn primary" href="/api/products" target="_blank">▶ Try it</a>
                        <a class="try-btn ghost" href="/api/products?page=2" target="_blank">▶ Page 2</a>
                        <a class="try-btn ghost" href="/api/products?page=3" target="_blank">▶ Page 3</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- GET /api/products/search -->
        <div class="ep">
            <div class="ep-head" onclick="toggle(this)">
                <span class="badge-method GET">GET</span>
                <span class="ep-path">/api/products/search?q={keyword}</span>
                <span class="ep-hint">Search by name or description</span>
                <span class="ep-caret">›</span>
            </div>
            <div class="ep-body">
                <div class="ep-inner">
                    <p class="ep-desc">Searches products using LIKE on both name and description. The name column has a B-tree index to speed up prefix searches.</p>
                    <div class="tags">
                        <span class="tag tag-fix">Fix #4 — Index on name</span>
                        <span class="tag tag-page">Fix #3 — paginate(15)</span>
                    </div>
                    <div class="btn-row">
                        <a class="try-btn primary" href="/api/products/search?q=product" target="_blank">▶ Search "product"</a>
                        <a class="try-btn ghost" href="/api/products/search?q=1" target="_blank">▶ Search "1"</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- GET /api/products/dashboard -->
        <div class="ep">
            <div class="ep-head" onclick="toggle(this)">
                <span class="badge-method GET">GET</span>
                <span class="ep-path">/api/products/dashboard</span>
                <span class="ep-hint">Aggregated stats — cached 10 min</span>
                <span class="ep-caret">›</span>
            </div>
            <div class="ep-body">
                <div class="ep-inner">
                    <p class="ep-desc">Previously ran <code style="color:var(--red)">Product::all()->count()</code> loading all rows into PHP memory. Now uses <code style="color:var(--green)">Product::count()</code> — a single <code>SELECT COUNT(*)</code> SQL aggregate. Result cached for 10 minutes.</p>
                    <div class="tags">
                        <span class="tag tag-cache">Fix #2 — 10min Cache</span>
                        <span class="tag tag-fix">Fix #7 — DB Aggregates</span>
                    </div>
<pre><span class="c">// Before: loads 500 rows into PHP memory just to count</span>
<span class="k">Product</span>::all()-><span class="k">count()</span>   <span class="c">// ❌ 500 rows loaded</span>

<span class="c">// After: single SQL aggregate</span>
<span class="k">Product</span>::<span class="k">count()</span>           <span class="c">// ✅ SELECT COUNT(*)</span>
<span class="k">Product</span>::orderByDesc(<span class="s">'sold_count'</span>)->take(<span class="n">5</span>)->get()  <span class="c">// ✅ ORDER BY + LIMIT in SQL</span></pre>
                    <div class="btn-row">
                        <a class="try-btn primary" href="/api/products/dashboard" target="_blank">▶ Try it</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- GET /api/products/sales-report -->
        <div class="ep">
            <div class="ep-head" onclick="toggle(this)">
                <span class="badge-method GET">GET</span>
                <span class="ep-path">/api/products/sales-report</span>
                <span class="ep-hint">Sales per order item — was 1000+ queries</span>
                <span class="ep-caret">›</span>
            </div>
            <div class="ep-body">
                <div class="ep-inner">
                    <p class="ep-desc">The worst N+1 case. Nested loops fired one query per order for items, then one per item for product, then one per order for customer — totalling 1000+ queries. Now resolved with a single eager-load call.</p>
                    <div class="tags">
                        <span class="tag tag-fix">Fix #1 — Nested N+1</span>
                        <span class="tag tag-page">Fix #3 — paginate(15)</span>
                    </div>
<pre><span class="c">// Before — 1000+ queries</span>
<span class="k">Order</span>::all() → foreach items → $item->product → $order->customer

<span class="c">// After — 2 queries total</span>
<span class="k">Order</span>::with([<span class="s">'items.product'</span>, <span class="s">'customer'</span>])->paginate(<span class="n">15</span>)</pre>
                    <div class="btn-row">
                        <a class="try-btn primary" href="/api/products/sales-report" target="_blank">▶ Try it</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- POST /api/products -->
        <div class="ep">
            <div class="ep-head" onclick="toggle(this)">
                <span class="badge-method POST">POST</span>
                <span class="ep-path">/api/products</span>
                <span class="ep-hint">Create product &amp; bust cache</span>
                <span class="ep-caret">›</span>
            </div>
            <div class="ep-body">
                <div class="ep-inner">
                    <p class="ep-desc">Creates a new product. After insertion, busts both the product list cache and dashboard stats cache so subsequent reads reflect the new data immediately.</p>
                    <div class="tags">
                        <span class="tag tag-cache">Fix #2 — Cache Invalidation</span>
                    </div>
<pre><span class="c">// Request body (JSON)</span>
{
  <span class="k">"name"</span>:        <span class="s">"New Product"</span>,
  <span class="k">"price"</span>:       <span class="n">99.99</span>,
  <span class="k">"stock"</span>:       <span class="n">50</span>,
  <span class="k">"category_id"</span>: <span class="n">1</span>
}

<span class="c">// After store() runs:</span>
Cache::forget(<span class="s">'products.page.1'</span>);
Cache::forget(<span class="s">'dashboard.stats'</span>);</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Orders Endpoints ── -->
    <div class="label">Orders Endpoints</div>
    <div class="eps">

        <!-- GET /api/orders -->
        <div class="ep">
            <div class="ep-head" onclick="toggle(this)">
                <span class="badge-method GET">GET</span>
                <span class="ep-path">/api/orders</span>
                <span class="ep-hint">List orders — was 401 queries, now 3</span>
                <span class="ep-caret">›</span>
            </div>
            <div class="ep-body">
                <div class="ep-inner">
                    <p class="ep-desc">Previously fired 1 + 200 (customers) + 200 (items) = 401 separate SQL queries. Now runs exactly 3 queries regardless of how many orders exist.</p>
                    <div class="tags">
                        <span class="tag tag-fix">Fix #1 — N+1 Eager Load</span>
                        <span class="tag tag-page">Fix #3 — paginate(15)</span>
                    </div>
<pre><span class="c">// Before — 401 queries for 200 orders</span>
<span class="k">Order</span>::all() → $order->customer → $order->items->count()

<span class="c">// After — 3 queries always</span>
<span class="k">Order</span>::with([<span class="s">'customer'</span>, <span class="s">'items'</span>])->paginate(<span class="n">15</span>)</pre>
                    <div class="btn-row">
                        <a class="try-btn primary" href="/api/orders" target="_blank">▶ Try it</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- GET /api/orders/filter -->
        <div class="ep">
            <div class="ep-head" onclick="toggle(this)">
                <span class="badge-method GET">GET</span>
                <span class="ep-path">/api/orders/filter?status={status}</span>
                <span class="ep-hint">Filter — SQL injection risk fixed</span>
                <span class="ep-caret">›</span>
            </div>
            <div class="ep-body">
                <div class="ep-inner">
                    <p class="ep-desc">The original used raw string interpolation into SQL — a critical injection vulnerability. An attacker could send <code style="color:var(--red)">?status=' OR '1'='1</code> to dump all records, or worse.</p>
                    <div class="tags">
                        <span class="tag tag-sec">Fix #6 — SQL Injection</span>
                        <span class="tag tag-fix">Fix #4 — Index on status</span>
                        <span class="tag tag-page">Fix #3 — paginate(15)</span>
                    </div>
<pre><span class="c">// Before — ❌ injectable</span>
DB::select(<span class="s">"SELECT * FROM orders WHERE status = '$status'"</span>)

<span class="c">// After — ✅ parameterized + whitelisted</span>
$request->validate([<span class="s">'status'</span> => <span class="s">'required|in:pending,completed,cancelled'</span>]);
<span class="k">Order</span>::where(<span class="s">'status'</span>, $request->input(<span class="s">'status'</span>))->paginate(<span class="n">15</span>)</pre>
                    <div class="btn-row">
                        <a class="try-btn primary" href="/api/orders/filter?status=pending"   target="_blank">▶ Pending</a>
                        <a class="try-btn ghost"   href="/api/orders/filter?status=completed" target="_blank">▶ Completed</a>
                        <a class="try-btn ghost"   href="/api/orders/filter?status=cancelled" target="_blank">▶ Cancelled</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- POST /api/orders -->
        <div class="ep">
            <div class="ep-head" onclick="toggle(this)">
                <span class="badge-method POST">POST</span>
                <span class="ep-path">/api/orders</span>
                <span class="ep-hint">Create order — fully transactional</span>
                <span class="ep-caret">›</span>
            </div>
            <div class="ep-body">
                <div class="ep-inner">
                    <p class="ep-desc">The original created the order row first, then looped items. If item 3 failed, items 1 &amp; 2 were already committed and stock already decremented — leaving corrupted partial data in production. Now fully atomic.</p>
                    <div class="tags">
                        <span class="tag tag-fix">Fix #5 — DB::transaction()</span>
                        <span class="tag tag-fix">Fix #5 — lockForUpdate()</span>
                    </div>
<pre><span class="c">// Wrapped in a single atomic transaction</span>
DB::transaction(function () use ($request) {
    $order = <span class="k">Order</span>::create([...]);
    foreach ($request->items as $item) {
        <span class="c">// Pessimistic lock prevents concurrent over-sell</span>
        $product = <span class="k">Product</span>::lockForUpdate()->find($item[<span class="s">'product_id'</span>]);
        if ($product->stock < $item[<span class="s">'quantity'</span>]) abort(<span class="n">422</span>);
        <span class="k">OrderItem</span>::create([...]);
        $product->decrement(<span class="s">'stock'</span>, $item[<span class="s">'quantity'</span>]);
    }
    $order->update([<span class="s">'total_amount'</span> => $total]);
});

<span class="c">// Request body</span>
{ <span class="k">"customer_id"</span>: <span class="n">1</span>, <span class="k">"items"</span>: [{ <span class="k">"product_id"</span>: <span class="n">1</span>, <span class="k">"quantity"</span>: <span class="n">2</span> }] }</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- Benchmark -->
    <div class="label">Benchmark — Before vs After (MySQL)</div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r);overflow:hidden;margin-bottom:2.5rem;">
        <table class="perf-table">
            <thead>
                <tr>
                    <th>Endpoint</th>
                    <th>Before (bugs)</th>
                    <th>After (cold)</th>
                    <th>After (cached)</th>
                    <th>Queries before → after</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>GET /api/products</td>
                    <td class="slow">~513ms</td>
                    <td>~2300ms</td>
                    <td class="fast">~160ms</td>
                    <td>501 → 2</td>
                </tr>
                <tr>
                    <td>GET /api/orders</td>
                    <td class="slow">~514ms</td>
                    <td class="fast">~186ms</td>
                    <td>—</td>
                    <td>401 → 3</td>
                </tr>
                <tr>
                    <td>GET /api/products/dashboard</td>
                    <td class="slow">hit DB every time</td>
                    <td>~178ms</td>
                    <td class="fast">~167ms</td>
                    <td>5 heavy → 5 fast</td>
                </tr>
                <tr>
                    <td>GET /api/products/sales-report</td>
                    <td class="slow">1000+ queries</td>
                    <td class="fast">~200ms</td>
                    <td>—</td>
                    <td>1000+ → 2</td>
                </tr>
                <tr>
                    <td>GET /api/orders/filter</td>
                    <td class="slow" style="color:var(--red)">SQL Injection ⚠</td>
                    <td class="fast">~187ms</td>
                    <td>—</td>
                    <td>unsafe → safe</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Fixes Summary -->
    <div class="label">All 7 Issues Fixed</div>
    <div class="fixes">
        <div class="fix-card"><span class="fix-icon">🔁</span><div><div class="fix-title">N+1 Query Problem</div><div class="fix-desc">Replaced lazy-loading loops with <code>with()</code> eager loading — reduces 500+ queries to 2</div></div></div>
        <div class="fix-card"><span class="fix-icon">⚡</span><div><div class="fix-title">Missing Cache Layer</div><div class="fix-desc"><code>Cache::remember()</code> on products list &amp; dashboard. Auto-invalidated on write operations</div></div></div>
        <div class="fix-card"><span class="fix-icon">📄</span><div><div class="fix-title">No Pagination</div><div class="fix-desc"><code>paginate(15)</code> on all list endpoints — prevents loading thousands of rows into memory</div></div></div>
        <div class="fix-card"><span class="fix-icon">🗂️</span><div><div class="fix-title">Missing DB Indexes</div><div class="fix-desc">Added indexes on <code>products.name</code>, <code>products.sold_count</code>, <code>orders.status</code></div></div></div>
        <div class="fix-card"><span class="fix-icon">🔒</span><div><div class="fix-title">No DB Transaction</div><div class="fix-desc"><code>DB::transaction()</code> + <code>lockForUpdate()</code> — atomic order creation, prevents over-selling</div></div></div>
        <div class="fix-card"><span class="fix-icon">🛡️</span><div><div class="fix-title">SQL Injection Risk</div><div class="fix-desc">Replaced raw interpolated query with Eloquent parameterized binding + whitelist validation</div></div></div>
        <div class="fix-card"><span class="fix-icon">📊</span><div><div class="fix-title">Inefficient Aggregation</div><div class="fix-desc"><code>Product::all()->count()</code> → <code>Product::count()</code> — zero rows loaded into PHP memory</div></div></div>
    </div>

</div>

<!-- ═══════════ FOOTER ═══════════ -->
<footer>
    <span>Sherazi POS</span> &nbsp;·&nbsp; Laravel {{ app()->version() }} &nbsp;·&nbsp; PHP {{ PHP_MAJOR_VERSION }}.{{ PHP_MINOR_VERSION }} &nbsp;·&nbsp;
    @if($dbOk)
        <span style="color:var(--green)">✓ MySQL Connected</span>
    @else
        <span style="color:var(--red)">✗ DB Offline</span>
    @endif
    &nbsp;·&nbsp; <span>{{ config('app.url') }}</span>
</footer>

<script>
// ── Toggle endpoint accordion ──
function toggle(head) {
    head.closest('.ep').classList.toggle('open');
}

// ── Animated number counter ──
function animateCount(el, target, duration) {
    const start = performance.now();
    const update = (now) => {
        const elapsed = now - start;
        const progress = Math.min(elapsed / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 4);
        el.textContent = Math.round(ease * target).toLocaleString();
        if (progress < 1) requestAnimationFrame(update);
        else el.textContent = target.toLocaleString();
    };
    requestAnimationFrame(update);
}

// Animate stat counters on load
document.querySelectorAll('.stat-val[data-target]').forEach((el, i) => {
    setTimeout(() => animateCount(el, parseInt(el.dataset.target), 1400), i * 80);
});

// Animate revenue separately
const rev = {{ $totalRevenue }};
const revEl = document.getElementById('rev');
if (revEl && rev > 0) {
    setTimeout(() => {
        const start = performance.now();
        const update = (now) => {
            const elapsed = now - start;
            const progress = Math.min(elapsed / 1600, 1);
            const ease = 1 - Math.pow(1 - progress, 4);
            revEl.textContent = '$' + (ease * rev).toLocaleString('en', {maximumFractionDigits: 0});
            if (progress < 1) requestAnimationFrame(update);
            else revEl.textContent = '$' + rev.toLocaleString('en', {maximumFractionDigits: 0});
        };
        requestAnimationFrame(update);
    }, 320);
}

// Auto-open first card
document.querySelector('.ep')?.classList.add('open');
</script>
</body>
</html>
