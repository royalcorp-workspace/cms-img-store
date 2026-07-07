<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('picking_lists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders');
            $table->foreignUuid('warehouse_id')->constrained('warehouses');
            $table->foreignUuid('picker_id')->nullable()->constrained('users');
            $table->enum('status', ['draft', 'pending', 'picking', 'picked', 'cancelled'])->default('draft');
            $table->integer('priority')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('picking_lists');
    }
};