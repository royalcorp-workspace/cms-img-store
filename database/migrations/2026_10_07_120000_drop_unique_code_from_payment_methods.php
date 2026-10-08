<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop unique constraint on table public.payment_methods (CASCADE will drop dependent index)
        DB::statement('ALTER TABLE public.payment_methods DROP CONSTRAINT IF EXISTS payment_methods_code_unique CASCADE');
        DB::statement('DROP INDEX IF EXISTS payment_methods_code_unique');

        // Recreate as standard non-unique index for fast querying
        DB::statement('CREATE INDEX IF NOT EXISTS payment_methods_code_idx ON public.payment_methods (code)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS payment_methods_code_idx');
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS payment_methods_code_unique ON public.payment_methods (code)');
    }
};
