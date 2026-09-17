<?php

namespace App\Console\Commands;

use App\Services\ProductExportService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class ExportProductsItemMaster extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:export-item-master 
                            {--output= : Path destination for the exported xlsx file}
                            {--search= : Search keyword filter}
                            {--category= : Category ID filter}
                            {--brand= : Brand ID filter}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export products and variants to Excel matching the Template Export Item Master format';

    /**
     * Execute the console command.
     */
    public function handle(ProductExportService $exportService): int
    {
        $this->info('Starting Item Master product export...');

        $outputPath = $this->option('output');
        if (!$outputPath) {
            $outputPath = storage_path('app/exports/Export_Item_Master_' . date('Ymd_His') . '.xlsx');
        }

        // Build mock request if options provided
        $queryParams = [];
        if ($search = $this->option('search')) {
            $queryParams['search'] = $search;
        }
        if ($cat = $this->option('category')) {
            $queryParams['category_id'] = $cat;
        }
        if ($brand = $this->option('brand')) {
            $queryParams['brand_id'] = $brand;
        }

        $request = new Request($queryParams);

        $this->info("Generating Excel file...");
        $startTime = microtime(true);

        $savedPath = $exportService->saveToFile($outputPath, $request);
        $duration = round(microtime(true) - $startTime, 2);
        $fileSizeKb = round(filesize($savedPath) / 1024, 2);

        $this->info("Export completed successfully in {$duration}s!");
        $this->line("File saved to: <comment>{$savedPath}</comment> ({$fileSizeKb} KB)");

        return Command::SUCCESS;
    }
}
