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
        Schema::create('lead_stages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name'); // e.g. New Leads, Pending, On Process, Delivery, Closing, Lost
            $table->string('color')->nullable(); // e.g. #FF0000
            $table->integer('order_index')->default(0);
            $table->boolean('is_system')->default(false); // to prevent deleting core stages
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_stages');
    }
};
