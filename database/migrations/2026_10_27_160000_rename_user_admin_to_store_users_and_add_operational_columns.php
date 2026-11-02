<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Mengembangkan tabel user_admin menjadi store_users untuk memisahkan
     * akun operasional retail/toko/sales dari customer B2C (users).
     */
    public function up(): void
    {
        // 1. Rename tabel user_admin -> store_users dalam schema user_managements
        DB::statement('ALTER TABLE IF EXISTS user_managements.user_admin RENAME TO store_users');

        // 2. Tambahkan kolom operasional pada tabel store_users
        Schema::table('user_managements.store_users', function (Blueprint $table) {
            if (!Schema::hasColumn('user_managements.store_users', 'store_id')) {
                $table->uuid('store_id')->nullable()->after('user_id');
            }

            if (!Schema::hasColumn('user_managements.store_users', 'username')) {
                $table->string('username', 100)->nullable()->unique()->after('name');
            }

            if (!Schema::hasColumn('user_managements.store_users', 'user_type')) {
                $table->string('user_type', 50)->default('store')->after('email');
            }

            if (!Schema::hasColumn('user_managements.store_users', 'sales_code')) {
                $table->string('sales_code', 50)->nullable()->after('phone');
            }

            if (!Schema::hasColumn('user_managements.store_users', 'is_owner')) {
                $table->boolean('is_owner')->default(false)->after('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop View backward compatibility
        DB::statement('DROP VIEW IF EXISTS user_managements.user_admin CASCADE');

        // 2. Drop Foreign Key constraint & indexes
        DB::statement('ALTER TABLE IF EXISTS user_managements.store_users DROP CONSTRAINT IF EXISTS store_users_store_id_foreign');
        DB::statement('DROP INDEX IF EXISTS user_managements.store_users_user_type_idx');
        DB::statement('DROP INDEX IF EXISTS user_managements.store_users_store_id_idx');

        // 3. Drop kolom tambahan
        Schema::table('user_managements.store_users', function (Blueprint $table) {
            if (Schema::hasColumn('user_managements.store_users', 'store_id')) {
                $table->dropColumn('store_id');
            }
            if (Schema::hasColumn('user_managements.store_users', 'username')) {
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('user_managements.store_users', 'user_type')) {
                $table->dropColumn('user_type');
            }
            if (Schema::hasColumn('user_managements.store_users', 'sales_code')) {
                $table->dropColumn('sales_code');
            }
            if (Schema::hasColumn('user_managements.store_users', 'is_owner')) {
                $table->dropColumn('is_owner');
            }
        });

        // 4. Rename kembali ke user_admin
        DB::statement('ALTER TABLE IF EXISTS user_managements.store_users RENAME TO user_admin');
    }
};
