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
        Schema::create('settlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('reference_id')->unique();
            $table->timestamp('settlement_date')->nullable();
            $table->decimal('gross_amount', 15, 2)->default(0);
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2)->default(0);
            $table->string('status')->default('pending'); // pending, success, failed
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Assuming orders table exists, add settlement_id column if needed.
        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'settlement_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->uuid('settlement_id')->nullable()->after('payment_status');
                // You can add foreign key if you want
                // $table->foreign('settlement_id')->references('id')->on('settlements')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'settlement_id')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('settlement_id');
            });
        }
        
        Schema::dropIfExists('settlements');
    }
};
