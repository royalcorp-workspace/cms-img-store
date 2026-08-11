<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            if (!Schema::hasColumn('users', 'is_guest')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->boolean('is_guest')->default(false)->after('password');
                });
            }

            if (!Schema::hasColumn('users', 'customer_type')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->smallInteger('customer_type')->default(1)->comment('Tipe customer (1 = biasa, 2 = reseller)')
                        ->after('is_guest');
                });
            }

            if (!Schema::hasColumn('users', 'membership_level')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('membership_level', 50)->nullable()->after('customer_type');
                });
            }

            if (!Schema::hasColumn('users', 'reseller_price_type')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('reseller_price_type', 50)->nullable()->after('membership_level');
                });
            }
        }

        if (Schema::hasTable('customers')) {
            if (!Schema::hasColumn('customers', 'customer_type')) {
                Schema::table('customers', function (Blueprint $table) {
                    $table->smallInteger('customer_type')->default(1)->comment('Tipe customer (1 = biasa, 2 = reseller)')
                        ->after('phone');
                });
            }

            if (!Schema::hasColumn('customers', 'membership_level')) {
                Schema::table('customers', function (Blueprint $table) {
                    $table->string('membership_level', 50)->nullable()->after('customer_type');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn(['is_guest', 'customer_type', 'membership_level', 'reseller_price_type']);
            });
        }

        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn(['customer_type', 'membership_level']);
            });
        }
    }
};
