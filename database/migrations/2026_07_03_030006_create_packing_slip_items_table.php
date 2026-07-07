<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('packing_slip_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('packing_slip_id')->constrained('packing_slips');
            $table->foreignUuid('order_item_id')->constrained('order_items');
            $table->foreignUuid('product_id')->constrained('products');
            $table->foreignUuid('product_variant_id')->nullable()->constrained('variants');
            $table->foreignUuid('warehouse_location_id')->nullable()->constrained('warehouse_locations');
            $table->integer('quantity_ordered');
            $table->integer('quantity_packed');
            $table->integer('box_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_slip_items');
    }
};