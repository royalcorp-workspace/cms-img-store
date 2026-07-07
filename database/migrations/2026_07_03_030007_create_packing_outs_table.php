<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('packing_outs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('packing_slip_id')->constrained('packing_slips');
            $table->foreignUuid('warehouse_id')->constrained('warehouses');
            $table->foreignUuid('packer_id')->nullable()->constrained('users');
            $table->enum('status', ['draft', 'ready', 'out', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_outs');
    }
};