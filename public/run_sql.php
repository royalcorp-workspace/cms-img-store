<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(Illuminate\Http\Request::capture());

try {
    Illuminate\Support\Facades\DB::statement('
        ALTER TABLE product_variants 
        ADD COLUMN IF NOT EXISTS width NUMERIC(10,2) NULL,
        ADD COLUMN IF NOT EXISTS length NUMERIC(10,2) NULL,
        ADD COLUMN IF NOT EXISTS height NUMERIC(10,2) NULL,
        ADD COLUMN IF NOT EXISTS weight NUMERIC(10,2) NULL,
        ADD COLUMN IF NOT EXISTS min_order_qty INTEGER NULL,
        ADD COLUMN IF NOT EXISTS status BOOLEAN DEFAULT TRUE;
    ');
    echo "Columns added successfully!";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
