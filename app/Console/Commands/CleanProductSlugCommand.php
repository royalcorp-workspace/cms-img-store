<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product\Product;
use Illuminate\Support\Str;

class CleanProductSlugCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:clean-slugs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean product names (remove size dimensions), generate slugs, and extract base product code from variants.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $products = Product::with('variants')->get();
        $count = 0;

        foreach ($products as $product) {
            $originalName = $product->name;
            
            // 1. Clean Name: Remove size patterns like " 190 X 115", "160x200", etc at the end
            $cleanName = preg_replace('/\s*\d+\s*[xX]\s*\d+(?:\s*[xX]\s*\d+)?\s*(cm)?\s*$/i', '', $originalName);
            $cleanName = trim($cleanName);

            // 2. Generate new slug
            $baseSlug = Str::slug($cleanName);
            $newSlug = $baseSlug;
            
            // Check for uniqueness
            while (Product::where('slug', $newSlug)->where('id', '!=', $product->id)->exists()) {
                $randomHash = strtolower(Str::random(4));
                $newSlug = $baseSlug . '-' . $randomHash;
            }

            // 3. Extract Base Product Code
            $segments = $product->segments ?? [];
            $segmentCode = '';
            for ($i = 1; $i <= 4; $i++) {
                if (!empty($segments[$i])) {
                    $segmentCode .= trim($segments[$i]);
                }
            }

            $baseCode = $product->code; // default to existing

            if (!empty($segmentCode)) {
                $baseCode = $segmentCode;
            } else {
                $skus = $product->variants->pluck('sku')->filter()->values()->toArray();
                if (count($skus) > 1) {
                // Find longest common prefix
                $prefix = $skus[0];
                foreach ($skus as $sku) {
                    while (strpos($sku, $prefix) !== 0) {
                        $prefix = substr($prefix, 0, -1);
                        if (empty($prefix)) break;
                    }
                }
                if (!empty($prefix)) {
                    $baseCode = rtrim($prefix, '-_ ');
                }
            } elseif (count($skus) === 1) {
                // Only 1 variant, strip trailing 5 or 6 digits if it looks like a size
                $sku = $skus[0];
                $stripped = preg_replace('/\d{5,6}$/', '', $sku);
                if (!empty($stripped)) {
                    $baseCode = rtrim($stripped, '-_ ');
                } else {
                    $baseCode = $sku;
                }
            }
            } // close else block for segments

            // Update product
            $product->update([
                'name' => $cleanName,
                'slug' => $newSlug,
                'code' => $baseCode
            ]);
            
            $this->info("Updated: '{$originalName}' -> '{$cleanName}' | Code: {$baseCode} | Slug: {$newSlug}");
            $count++;
        }

        $this->info("Successfully cleaned {$count} products.");
    }
}
