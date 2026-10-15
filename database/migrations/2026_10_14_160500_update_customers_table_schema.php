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
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'address_number')) {
                $table->dropColumn('address_number');
            }

            if (!Schema::hasColumn('customers', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('customer_code');
            }

            if (!Schema::hasColumn('customers', 'gender')) {
                $table->string('gender', 20)->nullable()->after('birth_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'address_number')) {
                $table->unsignedBigInteger('address_number')->nullable()->after('customer_code');
            }

            if (Schema::hasColumn('customers', 'gender')) {
                $table->dropColumn('gender');
            }

            if (Schema::hasColumn('customers', 'birth_date')) {
                $table->dropColumn('birth_date');
            }
        });
    }
};
