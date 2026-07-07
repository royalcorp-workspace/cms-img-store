<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('packing_slips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('picking_list_id')->nullable()->constrained('picking_lists');
            $table->foreignUuid('order_id')->constrained('orders');
            $table->foreignUuid('packer_id')->nullable()->constrained('users');
            $table->enum('status', ['draft', 'packing', 'packed', 'cancelled'])->default('draft');
            $table->integer('box_count')->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_slips');
    }
};