<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->integer('type')->default(1);
            $table->string('provider', 100)->nullable();
            $table->string('image', 255)->nullable();
            $table->boolean('has_charge')->default(false);
            $table->integer('charge_type')->nullable();
            $table->decimal('charge_value', 15, 2)->nullable();
            $table->string('charge_bearer', 50)->nullable();
            $table->decimal('minimum_amount', 15, 2)->nullable();
            $table->decimal('maximum_amount', 15, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('status')->default(1);
            $table->string('creator', 36)->nullable();
            $table->string('editor', 36)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamps();
            $table->index(['status', 'deleted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
