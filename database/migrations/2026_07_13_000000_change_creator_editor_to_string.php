<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Query all tables in the public schema that have 'creator' or 'editor' columns
        $columns = DB::select("
            SELECT table_name, column_name 
            FROM information_schema.columns 
            WHERE table_schema = 'public' 
              AND column_name IN ('creator', 'editor')
        ");

        foreach ($columns as $col) {
            $table = $col->table_name;
            $column = $col->column_name;
            
            // Alter column type to varchar(255) using USING clause for safe UUID casting in PostgreSQL
            DB::statement("ALTER TABLE \"{$table}\" ALTER COLUMN \"{$column}\" TYPE varchar(255) USING \"{$column}\"::varchar");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting to UUID is not strictly necessary or simple because usernames cannot be cast to UUID.
        // So we keep them as varchar(255) or do nothing.
    }
};
