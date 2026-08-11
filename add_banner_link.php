<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('brands', function (Blueprint $table) {
    if (!Schema::hasColumn('brands', 'banner_link')) {
        $table->string('banner_link', 1000)->nullable()->after('banner_mobile');
    }
});
echo "Column banner_link added successfully.\n";
