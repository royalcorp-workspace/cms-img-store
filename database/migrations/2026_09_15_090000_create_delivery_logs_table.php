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
        Schema::create('delivery_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->foreignUuid('delivery_id')->nullable()->constrained('deliveries')->nullOnDelete();
            $table->string('waybill_id')->nullable()->index();
            $table->string('biteship_order_id')->nullable()->index();
            $table->string('courier_code')->nullable();
            $table->string('event')->index()->comment('order.status, order.price, order.waybill_id');
            $table->string('status')->nullable()->comment('Status kurir (allocated, picking_up, dropping_off, delivered, etc.)');
            $table->decimal('price', 15, 2)->nullable()->comment('Actual shipping price from Biteship');
            $table->decimal('cash_on_delivery', 15, 2)->nullable();
            $table->string('location')->nullable();
            $table->text('note')->nullable();
            $table->json('payload')->nullable()->comment('Raw JSON webhook payload');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_logs');
    }
};
