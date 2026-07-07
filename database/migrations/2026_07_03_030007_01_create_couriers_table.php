<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('couriers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->integer('type')->default(1);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('creator', 36)->nullable();
            $table->string('editor', 36)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamps();
            $table->index(['is_active', 'deleted']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('couriers');
    }
};
