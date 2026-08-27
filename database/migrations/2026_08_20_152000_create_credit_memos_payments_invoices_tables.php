<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_memos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('credit_memo_number')->unique();
            $table->uuid('order_id')->index();
            $table->string('gateway')->nullable();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('success');
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('payment_number')->unique()->nullable();
            $table->uuid('order_id')->index();
            $table->string('gateway')->nullable();
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status')->default('success');
            $table->json('payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique();
            $table->uuid('order_id')->index();
            $table->uuid('customer_id')->nullable()->index();
            $table->uuid('courier_id')->nullable();
            $table->uuid('shipping_addresses_id')->nullable();
            
            $table->integer('status')->default(0);
            $table->string('payment_method')->nullable();
            $table->integer('payment_status')->default(0);
            $table->uuid('settlement_id')->nullable();
            
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('shipping_cost_subsidy', 15, 2)->default(0);
            $table->decimal('transaction_fee', 15, 2)->default(0);
            
            $table->uuid('voucher_id')->nullable();
            $table->decimal('voucher_nominal', 15, 2)->default(0);
            
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->string('creator', 36)->nullable();
            $table->string('editor', 36)->nullable();
            $table->boolean('deleted')->default(false);
            
            $table->timestamp('due_date')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id')->index();
            $table->uuid('product_id')->nullable();
            $table->uuid('product_variant_id')->nullable();
            $table->uuid('product_color_id')->nullable();
            $table->string('name', 255);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_nominal', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            $table->text('item_notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('credit_memos');
    }
};
