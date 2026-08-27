<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('void_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number')->unique();
            $table->uuid('customer_id')->nullable();
            $table->json('order_data'); // Store the entire order row as JSON
            $table->json('order_items_data'); // Store all order items as JSON
            $table->text('void_reason')->nullable();
            $table->timestamp('voided_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('void_orders');
    }
};
