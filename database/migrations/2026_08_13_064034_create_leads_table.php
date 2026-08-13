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
        Schema::create('leads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id'); // From img-db customers
            $table->uuid('stage_id'); // Foreign key to lead_stages
            $table->decimal('amount', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('lost_reason')->nullable(); // Why the lead was lost
            $table->timestamps();
            
            $table->foreign('stage_id')->references('id')->on('lead_stages')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
