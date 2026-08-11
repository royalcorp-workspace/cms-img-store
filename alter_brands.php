<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

Schema::table('brands', function (Blueprint $table) {
    if (!Schema::hasColumn('brands', 'banner_type')) {
        $table->tinyInteger('banner_type')->default(1)->after('logo')->comment('1: Image, 2: Embed');
    }
    if (!Schema::hasColumn('brands', 'embed_web')) {
        $table->text('embed_web')->nullable()->after('banner_mobile');
    }
    if (!Schema::hasColumn('brands', 'embed_mobile')) {
        $table->text('embed_mobile')->nullable()->after('embed_web');
    }
});
echo "Columns added to brands table successfully.\n";
