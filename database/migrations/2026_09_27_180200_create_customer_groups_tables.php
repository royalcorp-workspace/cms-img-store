<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('customer_groups')) {
            Schema::create('customer_groups', function (Blueprint $table) {
                $table->uuid('id')->default(DB::raw('gen_random_uuid()'))->primary();
                $table->string('name', 255);
                $table->string('slug', 255)->unique()->nullable();
                $table->text('description')->nullable();
                $table->decimal('discount_percent', 5, 2)->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('creator', 100)->nullable();
                $table->string('editor', 100)->nullable();
                $table->boolean('deleted')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('customer_group_members')) {
            Schema::create('customer_group_members', function (Blueprint $table) {
                $table->uuid('id')->default(DB::raw('gen_random_uuid()'))->primary();
                $table->uuid('customer_group_id');
                $table->uuid('customer_id');
                $table->string('creator', 100)->nullable();
                $table->string('editor', 100)->nullable();
                $table->boolean('deleted')->default(false);
                $table->timestamps();

                // $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('cascade');
                // $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->unique(['customer_group_id', 'customer_id']);
            });
        }

        if (!Schema::hasTable('voucher_customer_groups')) {
            Schema::create('voucher_customer_groups', function (Blueprint $table) {
                $table->uuid('id')->default(DB::raw('gen_random_uuid()'))->primary();
                $table->uuid('voucher_id');
                $table->uuid('customer_group_id');
                $table->string('creator', 100)->nullable();
                $table->string('editor', 100)->nullable();
                $table->boolean('deleted')->default(false);
                $table->timestamps();

                $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
                $table->foreign('customer_group_id')->references('id')->on('customer_groups')->onDelete('cascade');
                $table->unique(['voucher_id', 'customer_group_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_customer_groups');
        Schema::dropIfExists('customer_group_members');
        Schema::dropIfExists('customer_groups');
    }
};
