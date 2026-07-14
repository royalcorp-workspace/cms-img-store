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
        Blueprint::macro('createdAtTz', function ($precision = 0) {
            return $this->timestampTz('created_at', $precision)->nullable();
        });

        Blueprint::macro('updatedAtTz', function ($precision = 0) {
            return $this->timestampTz('updated_at', $precision)->nullable();
        });

        // ======================== USERS TABLE ========================
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 255)->unique();
            $table->string('name', 255);
            $table->string('phone', 50)->nullable();
            $table->string('password', 255);
            $table->string('avatar', 500)->nullable();
            $table->dateTime('email_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('remember_token', 100)->nullable();
            $table->string('google_id', 255)->nullable();
            $table->text('firebase_token')->nullable();
            $table->string('firebase_uid', 255)->nullable();
            $table->string('auth_provider', 50)->nullable();
            $table->string('photo_url', 255)->nullable();
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
        });

        // ======================== PERMISSIONS TABLE ========================
        Schema::create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('resource')->nullable();
            $table->string('action')->nullable();
            $table->string('group')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->createdAtTz();
            $table->updatedAtTz();

            $table->unique(['name', 'guard_name']);
        });

        // ======================== ROLES TABLE ========================
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('guard_name')->default('web');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->integer('level')->default(0);
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->uuid('parent_id')->nullable();
            $table->softDeletes();
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->unique(['name', 'guard_name']);
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('roles')->onDelete('set null');
        });

        // ======================== BRANDS TABLE ========================
        Schema::create('brands', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255)->unique();
            $table->string('slug', 255)->unique();
            $table->longText('description')->nullable();
            $table->string('logo', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->smallInteger('status')->default(1)->comment('Status brand (1=aktif, 0=nonaktif)');
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
        });

        // ======================== PRODUCT CATEGORIES TABLE ========================
        Schema::create('product_category', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->longText('description')->nullable();
            $table->string('image', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->longText('meta_description')->nullable();
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
        });

        Schema::table('product_category', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('product_category')->onDelete('set null');
        });

        // ======================== PRODUCTS TABLE ========================
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('category_id');
            $table->uuid('brand_id');
            $table->string('name', 255);
            $table->string('slug', 255)->unique();
            $table->string('thumbnail', 500)->nullable();
            $table->string('alt_text', 255)->nullable();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->boolean('best_seller')->default(false);
            $table->boolean('is_new')->default(false);
            $table->integer('sort_order')->default(0);
            $table->smallInteger('status')->default(1);
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->string('base_price', 255)->nullable();
            $table->string('uom', 255)->nullable();
            $table->json('segments')->nullable();
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('restrict');
            $table->foreign('category_id')->references('id')->on('product_category')->onDelete('restrict');
        });

        // ======================== PRODUCT TAGS TABLE ========================
        Schema::create('product_tags', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
        });

        // ======================== PRODUCT VARIANTS TABLE ========================
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id')->comment('ID Produk referensi');
            $table->string('variant_name', 255);
            $table->string('sku', 100)->nullable();
            $table->decimal('price', 15, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->json('attributes')->nullable();
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // ======================== WAREHOUSES TABLE ========================
        Schema::create('warehouses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->text('address')->nullable();
            $table->string('city', 50)->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // ======================== WAREHOUSE LOCATIONS TABLE ========================
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('warehouse_id')->constrained('warehouses');
            $table->string('code', 50);
            $table->string('name', 100);
            $table->smallInteger('type')->comment('Tipe lokasi (1=zone, 2=area, 3=rack, 4=shelf, 5=bin)');
            $table->uuid('parent_id')->nullable();
            $table->timestamps();
        });

        Schema::table('warehouse_locations', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('warehouse_locations');
        });

        // ======================== ABOUT US TABLE ========================
        Schema::create('about_us', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('company_name', 255);
            $table->string('tagline', 255)->nullable();
            $table->longText('description')->nullable();
            $table->longText('vision')->nullable();
            $table->longText('mission')->nullable();
            $table->longText('values')->nullable();
            $table->integer('established_year')->nullable();
            $table->longText('address')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email', 255)->nullable();
            $table->longText('logo')->nullable();
            $table->longText('cover_image')->nullable();
            $table->json('social_media')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
        });

        // ======================== ACTIVITY LOGS TABLE ========================
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('action')->comment('Aktivitas yang dilakukan (misal: login_success, login_failed, logout, dll)');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ======================== CITIES TABLE ========================
        Schema::create('cities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('province', 100);
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('creator', 100)->nullable();
            $table->string('editor', 100)->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            $table->string('province_id', 255)->nullable();
        });

        // ======================== ADDRESSES TABLE ========================
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->uuid('sub_district_id')->nullable();
            $table->uuid('city_id')->comment('ID Kota referensi');
            $table->string('label', 50)->comment('Label alamat (misal: Rumah, Kantor, Toko)');
            $table->string('recipient_name', 100);
            $table->string('phone', 20);
            $table->longText('address');
            $table->string('postal_code', 10)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->string('creator', 100)->nullable();
            $table->string('editor', 100)->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('restrict');
        });

        // ======================== BLOG POSTS TABLE ========================
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('slug', 255)->unique();
            $table->string('excerpt')->nullable();
            $table->text('content')->nullable();
            $table->string('featured_image', 255)->nullable();
            $table->string('author_name', 255)->nullable();
            $table->boolean('is_published')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->integer('sort_order')->default(0);
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description')->nullable();
            $table->string('creator')->nullable();
            $table->string('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });

        // ======================== CACHE TABLE ========================
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key', 255)->primary();
            $table->longText('value');
            $table->integer('expiration');
        });

        // ======================== CACHE LOCKS TABLE ========================
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key', 255)->primary();
            $table->string('owner', 255);
            $table->integer('expiration');
        });

        // ======================== COURIERS TABLE ========================
        Schema::create('couriers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->integer('type')->default(1)->comment('Tipe layanan kurir (1=reguler, 2=express, 3=same-day)');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('creator', 36)->nullable();
            $table->string('editor', 36)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamps();
            
            $table->index(['is_active', 'deleted']);
        });

        // ======================== SHIPPING ADDRESSES TABLE ========================
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('courier_id');
            $table->uuid('sub_district_id')->nullable();
            $table->integer('type')->default(1);
            $table->integer('price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('creator', 36)->nullable();
            $table->string('editor', 36)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamps();

            $table->foreign('courier_id')->references('id')->on('couriers')->onDelete('cascade');
            $table->index(['is_active', 'deleted']);
        });

        // ======================== CUSTOMERS TABLE ========================
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->longText('meta')->nullable();
            $table->string('creator', 100)->nullable();
            $table->string('editor', 100)->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // ======================== VOUCHERS TABLE ========================
        Schema::create('vouchers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->string('title', 200);
            $table->longText('description')->nullable();
            $table->smallInteger('type')->default(2);
            $table->decimal('value', 12, 2);
            $table->decimal('min_purchase', 12, 2)->default(0.00);
            $table->decimal('max_discount', 12, 2)->nullable();
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_limit_per_user')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('valid_for_new_customer')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('creator', 100)->nullable();
            $table->string('editor', 100)->nullable();
            $table->boolean('deleted')->default(false);
            $table->smallInteger('scope')->default(1);
            $table->boolean('allow_stacking')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
        });

        // ======================== ORDERS TABLE ========================
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            $table->integer('status')->default(0)->comment('Status pesanan (0=Draft, 1=Pending Approval, 2=Confirmed, 3=Processing, 4=Shipped, 5=Delivered, 6=Cancelled, 7=Returned)');
            $table->string('payment_method', 100)->nullable();
            $table->integer('payment_status')->default(0)->comment('Status pembayaran (0=Unpaid, 1=Paid, 2=Failed, 3=Refunded, 4=Partial)');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->string('creator', 36)->nullable();
            $table->string('editor', 36)->nullable();
            $table->boolean('deleted')->default(false);
            $table->uuid('voucher_id')->nullable();
            $table->decimal('transaction_fee', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('set null');
        });

        // ======================== ORDER ITEMS TABLE ========================
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('product_id');
            $table->uuid('product_variant_id')->nullable();
            $table->string('name', 255);
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('discount_nominal', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->text('item_notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
            $table->foreign('product_variant_id')->references('id')->on('product_variants')->onDelete('restrict');
        });

        // ======================== PICKING LISTS TABLE ========================
        Schema::create('picking_lists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained('orders');
            $table->foreignUuid('warehouse_id')->constrained('warehouses');
            $table->foreignUuid('picker_id')->nullable()->constrained('users');
            $table->smallInteger('status')->default(1)->comment('Status picking (1=draft, 2=pending, 3=picking, 4=picked, 5=cancelled)');
            $table->integer('priority')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ======================== PICKING LIST ITEMS TABLE ========================
        Schema::create('picking_list_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('picking_list_id')->constrained('picking_lists');
            $table->foreignUuid('order_item_id')->constrained('order_items');
            $table->foreignUuid('product_id')->constrained('products');
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants');
            $table->foreignUuid('warehouse_location_id')->nullable()->constrained('warehouse_locations');
            $table->integer('quantity_ordered')->comment('Jumlah barang yang dipesan');
            $table->integer('quantity_picked')->default(0)->comment('Jumlah barang yang sudah dipicking');
            $table->smallInteger('status')->default(1)->comment('Status picking item (1=pending, 2=picked, 3=partial, 4=not_available)');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ======================== PACKING SLIPS TABLE ========================
        Schema::create('packing_slips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('picking_list_id')->nullable()->constrained('picking_lists');
            $table->foreignUuid('order_id')->constrained('orders');
            $table->foreignUuid('packer_id')->nullable()->constrained('users');
            $table->smallInteger('status')->default(1)->comment('Status packing (1=draft, 2=packing, 3=packed, 4=cancelled)');
            $table->integer('box_count')->default(0);
            $table->decimal('weight', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ======================== PACKING SLIP ITEMS TABLE ========================
        Schema::create('packing_slip_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('packing_slip_id')->constrained('packing_slips');
            $table->foreignUuid('order_item_id')->constrained('order_items');
            $table->foreignUuid('product_id')->constrained('products');
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants');
            $table->foreignUuid('warehouse_location_id')->nullable()->constrained('warehouse_locations');
            $table->integer('quantity_ordered');
            $table->integer('quantity_packed');
            $table->integer('box_number')->default(1);
            $table->timestamps();
        });

        // ======================== PACKING OUTS TABLE ========================
        Schema::create('packing_outs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('packing_slip_id')->constrained('packing_slips');
            $table->foreignUuid('warehouse_id')->constrained('warehouses');
            $table->foreignUuid('packer_id')->nullable()->constrained('users');
            $table->smallInteger('status')->default(1)->comment('Status packing out (1=draft, 2=ready, 3=out, 4=cancelled)');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ======================== DELIVERIES TABLE ========================
        Schema::create('deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('packing_out_id')->nullable()->constrained('packing_outs');
            $table->foreignUuid('order_id')->constrained('orders');
            $table->foreignUuid('courier_id')->nullable()->constrained('couriers');
            $table->string('tracking_number', 100)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_phone', 50)->nullable();
            $table->smallInteger('status')->default(1)->comment('Status pengiriman (1=pending, 2=in_transit, 3=delivered, 4=failed, 5=returned)');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ======================== DELIVERY ITEMS TABLE ========================
        Schema::create('delivery_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('delivery_id');
            $table->uuid('packing_slip_item_id')->nullable();
            $table->uuid('product_id');
            $table->uuid('product_variant_id')->nullable();
            $table->integer('quantity_delivered');
            $table->longText('notes')->nullable();
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('delivery_id')->references('id')->on('deliveries')->onDelete('cascade');
        });

        // ======================== DEVICE SESSIONS TABLE ========================
        Schema::create('device_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('user_email', 255)->nullable();
            $table->string('session_id', 255)->nullable();
            $table->uuid('refresh_token_id')->nullable();
            $table->string('device_name', 255);
            $table->string('device_type', 100)->comment('Tipe perangkat (misal: android, ios, web)');
            $table->string('ip_address', 45)->nullable();
            $table->longText('user_agent')->nullable();
            $table->dateTime('last_active_at')->nullable();
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // ======================== EMAIL VERIFICATIONS TABLE ========================
        Schema::create('email_verifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('token', 255)->unique();
            $table->dateTime('expires_at');
            $table->boolean('used')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ======================== FAILED JOBS TABLE ========================
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id('id');
            $table->string('uuid', 255)->unique();
            $table->longText('connection');
            $table->longText('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->createdAtTz();
        });

        // ======================== JOB BATCHES TABLE ========================
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->text('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->createdAtTz();
            $table->updatedAtTz();
        });

        // ======================== JOBS TABLE ========================
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue')->index();
            $table->longText('payload');
            $table->tinyInteger('attempts')->default(0);
            $table->integer('reserved_at')->nullable();
            $table->integer('available_at');
            $table->integer('created_at');
        });

        // ======================== LOGIN ATTEMPTS TABLE ========================
        Schema::create('login_attempts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('email', 255);
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->boolean('successful')->default(false)->comment('Status keberhasilan login (true=berhasil, false=gagal)');
            $table->string('failure_reason')->nullable()->comment('Alasan gagal login (misal: wrong_password, email_not_found)');
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        // ======================== MENUS TABLE ========================
        Schema::create('menus', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title', 255);
            $table->string('icon', 255)->nullable();
            $table->string('route_name', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->string('permission', 255)->nullable();
            $table->uuid('parent_id')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->createdAtTz();
            $table->updatedAtTz();
        });

        Schema::table('menus', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('menus')->onDelete('set null');
        });

        // ======================== MENU ITEMS TABLE ========================
        Schema::create('menu_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('menu_id');
            $table->string('title', 255);
            $table->string('icon', 255)->nullable();
            $table->string('route_name', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->string('permission', 255)->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->createdAtTz();
            $table->updatedAtTz();
        });

        Schema::table('menu_items', function (Blueprint $table) {
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
        });

        // ======================== MODEL HAS PERMISSIONS TABLE ========================
        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->uuid('permission_id');
            $table->createdAtTz();
            
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->unique(['model_id', 'model_type', 'permission_id']);
        });

        // ======================== MODEL HAS ROLES TABLE ========================
        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('model_type');
            $table->uuid('model_id');
            $table->uuid('role_id');
            $table->createdAtTz();
            
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->unique(['model_id', 'model_type', 'role_id']);
        });

        // ======================== OTP TABLE ========================
        Schema::create('otps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->comment('ID User referensi');
            $table->string('code', 6);
            $table->string('channel', 50)->comment('Saluran pengiriman OTP (misal: email, sms)');
            $table->integer('attempts')->default(0);
            $table->dateTime('expires_at');
            $table->boolean('verified')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ======================== PASSWORD RESETS TABLE ========================
        Schema::create('password_resets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('token', 255)->unique();
            $table->dateTime('expires_at');
            $table->boolean('used')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ======================== PERSONAL ACCESS TOKENS TABLE ========================
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id('id');
            $table->string('tokenable_type');
            $table->uuid('tokenable_id');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->createdAtTz();
            $table->updatedAtTz();
        });

        // ======================== PRODUCT COLORS TABLE ========================
        Schema::create('product_colors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->string('color_name', 100);
            $table->string('color_code', 20)->nullable();
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        // ======================== PRODUCT TAG RELATIONS TABLE ========================
        Schema::create('product_tag_relations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->uuid('tag_id');
            $table->createdAtTz();
            
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('product_tags')->onDelete('cascade');
            $table->unique(['product_id', 'tag_id']);
        });

        // ======================== PRODUCT STOCKS TABLE ========================
        Schema::create('product_stocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products');
            $table->foreignUuid('product_variant_id')->nullable()->constrained('product_variants');
            $table->foreignUuid('warehouse_location_id')->constrained('warehouse_locations');
            $table->integer('quantity')->default(0);
            $table->integer('reserved_quantity')->default(0);
            $table->timestamps();
        });

        // ======================== PAYMENT METHODS TABLE ========================
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->integer('type')->default(1)->comment('Tipe metode pembayaran');
            $table->string('provider', 100)->nullable();
            $table->string('image', 255)->nullable();
            $table->boolean('has_charge')->default(false);
            $table->integer('charge_type')->nullable()->comment('Tipe biaya tambahan (jika ada)');
            $table->decimal('charge_value', 15, 2)->nullable();
            $table->string('charge_bearer', 50)->nullable();
            $table->decimal('minimum_amount', 15, 2)->nullable();
            $table->decimal('maximum_amount', 15, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->integer('status')->default(1)->comment('Status keaktifan metode pembayaran (1=aktif, 0=nonaktif)');
            $table->string('creator', 36)->nullable();
            $table->string('editor', 36)->nullable();
            $table->boolean('deleted')->default(false);
            $table->timestamps();
            
            $table->index(['status', 'deleted']);
        });

        // ======================== REFRESH TOKENS TABLE ========================
        Schema::create('refresh_tokens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('token', 500)->unique();
            $table->dateTime('expires_at');
            $table->boolean('revoked')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ======================== ROLE HAS PERMISSIONS TABLE ========================
        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('permission_id');
            $table->uuid('role_id');
            $table->createdAtTz();
            
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->unique(['permission_id', 'role_id']);
        });

        // ======================== USER ADMIN TABLE ========================
        Schema::create('user_admin', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('name', 255);
            $table->string('email', 255)->unique();
            $table->string('phone', 50)->nullable();
            $table->string('password_hash', 255);
            $table->boolean('email_verified')->default(false);
            $table->dateTime('email_verified_at')->nullable();
            $table->rememberToken()->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('creator')->nullable();
            $table->uuid('editor')->nullable();
            $table->boolean('deleted')->default(false);
            $table->createdAtTz();
            $table->updatedAtTz();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ======================== VOUCHER CATEGORIES TABLE ========================
        Schema::create('voucher_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('voucher_id');
            $table->uuid('category_id');
            $table->createdAtTz();
            
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('product_category')->onDelete('cascade');
            $table->unique(['voucher_id', 'category_id']);
        });

        // ======================== VOUCHER PRODUCTS TABLE ========================
        Schema::create('voucher_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('voucher_id');
            $table->uuid('product_id');
            $table->createdAtTz();
            
            $table->foreign('voucher_id')->references('id')->on('vouchers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unique(['voucher_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order to handle foreign key constraints safely
        Schema::dropIfExists('voucher_products');
        Schema::dropIfExists('voucher_categories');
        Schema::dropIfExists('vouchers');
        Schema::dropIfExists('user_admin');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('refresh_tokens');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('product_tag_relations');
        Schema::dropIfExists('product_colors');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('password_resets');
        Schema::dropIfExists('otps');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('login_attempts');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('email_verifications');
        Schema::dropIfExists('device_sessions');
        Schema::dropIfExists('delivery_items');
        Schema::dropIfExists('deliveries');
        Schema::dropIfExists('packing_outs');
        Schema::dropIfExists('packing_slip_items');
        Schema::dropIfExists('packing_slips');
        Schema::dropIfExists('picking_list_items');
        Schema::dropIfExists('picking_lists');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('shipping_addresses');
        Schema::dropIfExists('couriers');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('about_us');
        Schema::dropIfExists('warehouse_locations');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_tags');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_category');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('users');
    }
};
