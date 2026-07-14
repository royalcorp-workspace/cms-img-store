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
        // ======================== PROVINCES TABLE ========================
        Schema::create('provinces', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('code', 20)->nullable()->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('creator', 100)->nullable();
            $table->string('editor', 100)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== SUB DISTRICTS TABLE ========================
        Schema::create('sub_districts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('province', 100);
            $table->string('province_id', 255)->nullable();
            $table->foreignUuid('city_id')->constrained('cities')->onDelete('restrict');
            $table->string('district', 100);
            $table->string('sub_district', 100);
            $table->string('postal_code', 10);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('creator', 100)->nullable();
            $table->string('editor', 100)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== STORE GROUP TABLE ========================
        Schema::create('store_group', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 100)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== STORE TIER TABLE ========================
        Schema::create('store_tier', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 100)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->integer('level')->default(1);
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== STORE TABLE ========================
        Schema::create('store', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_group_id')->constrained('store_group')->onDelete('restrict');
            $table->foreignUuid('tier_id')->nullable()->constrained('store_tier')->onDelete('set null');
            $table->string('code', 100)->unique();
            $table->string('name', 255);
            $table->foreignUuid('owner_user_id')->nullable()->constrained('user_admin')->onDelete('set null');
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->decimal('outstanding_balance', 15, 2)->default(0.00);
            $table->text('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->json('documents')->nullable();
            $table->integer('payment_term')->default(0);
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== STORE CHANNEL GROUP TABLE ========================
        Schema::create('store_channel_group', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 100)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== STORE CHANNEL TABLE ========================
        Schema::create('store_channel', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('store_id')->constrained('store')->onDelete('restrict');
            $table->foreignUuid('store_channel_group_id')->constrained('store_channel_group')->onDelete('restrict');
            $table->string('code', 100)->unique();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== PRODUCT IMAGES TABLE ========================
        Schema::create('product_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->string('image', 500);
            $table->string('alt_text', 255)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('status')->default(true);
            $table->string('url', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        // ======================== REVIEWS TABLE ========================
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->uuid('order_id')->nullable();
            $table->string('user_name', 255);
            $table->string('user_email', 255)->nullable();
            $table->smallInteger('rating');
            $table->text('text')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_published')->default(false);
            $table->integer('report_count')->default(0);
            $table->timestamp('created_at', 0)->nullable();
            $table->timestamp('updated_at', 0)->nullable();
            $table->string('deleted_at', 255)->nullable();
            $table->boolean('is_deleted')->nullable()->default(false);
        });

        // ======================== FAQS TABLE ========================
        Schema::create('faqs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('question');
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->integer('view_count')->default(0);
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== HOW TO RETURNS TABLE ========================
        Schema::create('how_to_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('content');
            $table->jsonb('steps')->nullable();
            $table->text('featured_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== PRIVACY POLICIES TABLE ========================
        Schema::create('privacy_policies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('content');
            $table->string('version', 50)->nullable();
            $table->date('effective_date')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== TERMS AND CONDITIONS TABLE ========================
        Schema::create('terms_and_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('content');
            $table->string('version', 50)->nullable();
            $table->date('effective_date')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== WARRANTY CLAIMS TABLE ========================
        Schema::create('warranty_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->text('content');
            $table->jsonb('steps')->nullable();
            $table->jsonb('required_documents')->nullable();
            $table->integer('processing_time_days')->nullable();
            $table->text('featured_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->text('meta_description')->nullable();
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== PRICE PRODUCT SETTINGS TABLE ========================
        Schema::create('price_product_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->smallInteger('type')->default(1);
            $table->smallInteger('scope')->default(2);
            $table->smallInteger('discount_type')->nullable();
            $table->decimal('discount_value', 10, 2)->default(0.00);
            $table->decimal('min_purchase', 12, 2)->default(0.00);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->string('creator', 100)->nullable();
            $table->string('editor', 100)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->integer('scope_store_type')->nullable();
            $table->uuid('scope_store_id')->nullable();
        });

        // ======================== PRICE PRODUCT SETTING ITEMS TABLE ========================
        Schema::create('price_product_setting_items', function (Blueprint $table) {
            $table->foreignUuid('price_product_setting_id')->constrained('price_product_settings')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignUuid('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->smallInteger('discount_type')->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->string('creator', 100)->nullable();
            $table->string('editor', 100)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->primary(['price_product_setting_id', 'product_id']);
        });

        // ======================== PRICE PRODUCT SETTING STORE TABLE ========================
        Schema::create('price_product_setting_store', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('price_product_setting_id')->constrained('price_product_settings')->onDelete('cascade');
            $table->foreignUuid('store_id')->constrained('store')->onDelete('cascade');
            $table->foreignUuid('store_channel_id')->nullable()->constrained('store_channel')->onDelete('set null');
            $table->foreignUuid('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignUuid('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->jsonb('adjustments')->default('[]')->nullable();
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== PRICE PRODUCT SETTING TIER TABLE ========================
        Schema::create('price_product_setting_tier', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('price_product_setting_id')->constrained('price_product_settings')->onDelete('cascade');
            $table->foreignUuid('store_tier_id')->constrained('store_tier')->onDelete('cascade');
            $table->foreignUuid('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->foreignUuid('variant_id')->nullable()->constrained('product_variants')->onDelete('cascade');
            $table->jsonb('adjustments')->default('[]')->nullable();
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== PRICE PRODUCT SETTING VOLUME TIERS TABLE ========================
        Schema::create('price_product_setting_volume_tiers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('price_product_setting_id')->constrained('price_product_settings')->onDelete('cascade');
            $table->integer('min_purchase');
            $table->smallInteger('discount_type')->default(1);
            $table->integer('discount_value')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestampTz('created_at', 0)->nullable();
            $table->timestampTz('updated_at', 0)->nullable();
        });

        // ======================== VOUCHER USAGES TABLE ========================
        Schema::create('voucher_usages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('voucher_id')->constrained('vouchers')->onDelete('cascade');
            $table->foreignUuid('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('order_id', 100);
            $table->decimal('discount_amount', 12, 2);
            $table->string('creator', 100)->nullable();
            $table->string('editor', 100)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });

        // ======================== HAND OVERS TABLE ========================
        Schema::create('hand_overs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('packing_out_id')->constrained('packing_outs')->onDelete('cascade');
            $table->foreignUuid('order_id')->constrained('orders')->onDelete('restrict');
            $table->foreignUuid('warehouse_id')->constrained('warehouses')->onDelete('restrict');
            $table->foreignUuid('courier_id')->nullable()->constrained('couriers')->onDelete('restrict');
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->string('tracking_number')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('handover_at')->nullable();
            $table->timestamps();
        });

        // ======================== HAND OVERS ITEMS TABLE ========================
        Schema::create('hand_overs_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('handover_id')->constrained('hand_overs')->onDelete('cascade');
            $table->foreignUuid('order_item_id')->constrained('order_items')->onDelete('restrict');
            $table->foreignUuid('product_id')->constrained('products')->onDelete('restrict');
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants')->onDelete('restrict');
            $table->integer('quantity_ordered');
            $table->integer('quantity_handed_over');
            $table->foreignUuid('warehouse_location_id')->nullable()->constrained('warehouse_locations')->onDelete('restrict');
            $table->string('status')->default('handed_over');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hand_overs_items');
        Schema::dropIfExists('hand_overs');
        Schema::dropIfExists('voucher_usages');
        Schema::dropIfExists('price_product_setting_volume_tiers');
        Schema::dropIfExists('price_product_setting_tier');
        Schema::dropIfExists('price_product_setting_store');
        Schema::dropIfExists('price_product_setting_items');
        Schema::dropIfExists('price_product_settings');
        Schema::dropIfExists('warranty_claims');
        Schema::dropIfExists('terms_and_conditions');
        Schema::dropIfExists('privacy_policies');
        Schema::dropIfExists('how_to_returns');
        Schema::dropIfExists('faqs');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('store_channel');
        Schema::dropIfExists('store_channel_group');
        Schema::dropIfExists('store');
        Schema::dropIfExists('store_tier');
        Schema::dropIfExists('store_group');
        Schema::dropIfExists('sub_districts');
        Schema::dropIfExists('provinces');
    }
};
