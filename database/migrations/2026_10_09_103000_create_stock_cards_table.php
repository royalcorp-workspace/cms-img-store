<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('stock_cards')) {
            Schema::create('stock_cards', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->uuid('inventory_id')->nullable()->index();
                $table->uuid('product_id')->nullable()->index();
                $table->uuid('product_variant_id')->index();
                $table->uuid('warehouse_id')->nullable()->index();
                $table->uuid('store_channel_id')->nullable()->index();
                
                // Transaction details
                $table->string('transaction_type', 50)->index(); // incoming, outgoing, table_edit, import, adjustment, web_order, web_order_shipped, web_order_delivered, web_order_cancelled
                $table->string('reference_type', 100)->nullable(); // manual, import, web_order, table_edit, etc.
                $table->string('reference_number', 100)->nullable()->index(); // batch id, order number, etc.
                
                // Quantities
                $table->integer('qty_in')->default(0);
                $table->integer('qty_out')->default(0);
                $table->integer('stock_before')->default(0);
                $table->integer('stock_after')->default(0);
                
                // Extra metadata & audit
                $table->text('notes')->nullable();
                $table->string('creator', 100)->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_cards');
    }
};
