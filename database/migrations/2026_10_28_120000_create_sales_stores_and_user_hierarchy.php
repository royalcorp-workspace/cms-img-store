<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 1. Relasi Sales Canvasser ke Banyak Toko Binaan (store.sales_stores).
     * 2. Hierarki User Tree Komisi (Sales -> Owner Toko -> SPG Toko & SPG Internal).
     */
    public function up(): void
    {
        // 1. Buat tabel store.sales_stores
        if (!Schema::hasTable('store.sales_stores')) {
            Schema::create('store.sales_stores', function (Blueprint $table) {
                $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
                $table->uuid('sales_user_id');
                $table->uuid('store_id');
                $table->date('assigned_date')->default(DB::raw('CURRENT_DATE'));
                $table->boolean('is_primary')->default(true);
                $table->timestamps();

                $table->unique(['sales_user_id', 'store_id'], 'sales_stores_sales_store_unique');
                $table->index('sales_user_id', 'sales_stores_sales_user_id_idx');
                $table->index('store_id', 'sales_stores_store_id_idx');

                $table->foreign('sales_user_id')
                    ->references('id')
                    ->on('user_managements.store_users')
                    ->onDelete('cascade');

                $table->foreign('store_id')
                    ->references('id')
                    ->on('store.store')
                    ->onDelete('cascade');
            });
        }

        // 2. Tambahkan kolom hierarki User Tree pada user_managements.store_users
        Schema::table('user_managements.store_users', function (Blueprint $table) {
            if (!Schema::hasColumn('user_managements.store_users', 'role_persona')) {
                $table->string('role_persona', 50)->nullable()->after('user_type');
            }

            if (!Schema::hasColumn('user_managements.store_users', 'parent_user_id')) {
                $table->uuid('parent_user_id')->nullable()->after('store_id');
            }
        });

        // 3. Foreign key recursive untuk parent_user_id
        DB::statement('
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1 FROM pg_constraint WHERE conname = \'store_users_parent_user_id_foreign\'
                ) THEN
                    ALTER TABLE user_managements.store_users
                    ADD CONSTRAINT store_users_parent_user_id_foreign
                    FOREIGN KEY (parent_user_id) REFERENCES user_managements.store_users(id) ON DELETE SET NULL;
                END IF;
            END
            $$;
        ');

        DB::statement('CREATE INDEX IF NOT EXISTS store_users_role_persona_idx ON user_managements.store_users(role_persona)');
        DB::statement('CREATE INDEX IF NOT EXISTS store_users_parent_user_id_idx ON user_managements.store_users(parent_user_id)');

        // 4. Perbarui view user_admin
        DB::statement('CREATE OR REPLACE VIEW user_managements.user_admin AS SELECT * FROM user_managements.store_users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store.sales_stores');

        Schema::table('user_managements.store_users', function (Blueprint $table) {
            if (Schema::hasColumn('user_managements.store_users', 'parent_user_id')) {
                $table->dropForeign(['parent_user_id']);
                $table->dropColumn('parent_user_id');
            }
            if (Schema::hasColumn('user_managements.store_users', 'role_persona')) {
                $table->dropColumn('role_persona');
            }
        });

        DB::statement('CREATE OR REPLACE VIEW user_managements.user_admin AS SELECT * FROM user_managements.store_users');
    }
};
