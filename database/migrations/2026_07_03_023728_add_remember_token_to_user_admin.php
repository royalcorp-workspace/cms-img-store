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
        Schema::table('user_admin', function (Blueprint $table) {
            // Wajib ada agar Auth::attempt($creds, $remember) bisa buat remember_me cookie
            $table->rememberToken()->nullable()->after('password_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_admin', function (Blueprint $table) {
            $table->dropColumn('remember_token');
        });
    }
};
