<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('picking_list_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('picking_list_id')->constrained('picking_lists');
            $table->foreignUuid('order_item_id')->constrained('order_items');
            $table->foreignUuid('product_id')->constrained('products');
            $table->foreignUuid('product_variant_id')->nullable()->constrained('variants');
            $table->foreignUuid('warehouse_location_id')->nullable()->constrained('warehouse_locations');
            $table->integer('quantity_ordered');
            $table->integer('quantity_picked')->default(0);
            $table->enum('status', ['pending', 'picked', 'partial', 'not_available'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('picking_list_items');
    }
};