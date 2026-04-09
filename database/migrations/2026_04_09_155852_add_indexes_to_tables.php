<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FIX #4 — Database Indexing
 * Adds missing indexes on columns used in WHERE / ORDER BY / LIKE queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        // products.name — used in LIKE search → fulltext or regular index
        Schema::table('products', function (Blueprint $table) {
            $table->index('name', 'idx_products_name');
            $table->index('sold_count', 'idx_products_sold_count');
        });

        // orders.status — used in WHERE filter
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status', 'idx_orders_status');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_name');
            $table->dropIndex('idx_products_sold_count');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status');
        });
    }
};
