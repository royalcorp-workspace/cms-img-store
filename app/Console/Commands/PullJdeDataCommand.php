<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\JdePullService;
use Illuminate\Console\Command;

class PullJdeDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jde:pull 
                            {type=all : Tipe data yang ditarik (item_master, base_price, branch, customer, all)}
                            {--file= : Path ke file dump JSON lokal jika offline}
                            {--limit=0 : Batasi jumlah data yang diproses}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menarik data mentah dari Oracle JDE ERP ke tabel temporary staging (Metode Cron / CLI)';

    public function handle(JdePullService $service): int
    {
        $type = strtolower((string) $this->argument('type'));
        $file = $this->option('file') ? (string) $this->option('file') : null;
        $limit = (int) $this->option('limit');

        $this->info("=== Memulai Penarikan Data JDE (Tipe: {$type}) ===");

        switch ($type) {
            case 'item_master':
            case 'item':
                $this->info('Menarik data Item Master...');
                $res = $service->pullItemMasters($file, $limit);
                $this->outputResult($res);
                break;

            case 'base_price':
            case 'price':
                $this->info('Menarik data Base Price...');
                $res = $service->pullBasePrices($file, $limit);
                $this->outputResult($res);
                break;

            case 'branch':
                $this->info('Menarik data Branch Plant...');
                $res = $service->pullBranches($file, $limit);
                $this->outputResult($res);
                break;

            case 'customer':
            case 'cust':
                $this->info('Menarik data Customer / Toko Retail...');
                $res = $service->pullCustomers($file, $limit);
                $this->outputResult($res);
                break;

            case 'all':
            default:
                $this->info('1/4. Menarik Branch Plant...');
                $resBranch = $service->pullBranches($file, $limit);
                $this->outputResult($resBranch);

                $this->info('2/4. Menarik Customer / Toko Retail...');
                $resCust = $service->pullCustomers($file, $limit);
                $this->outputResult($resCust);

                $this->info('3/4. Menarik Item Master Kasur...');
                $resItem = $service->pullItemMasters($file, $limit);
                $this->outputResult($resItem);

                $this->info('4/4. Menarik Base Price Kasur...');
                $resPrice = $service->pullBasePrices($file, $limit);
                $this->outputResult($resPrice);
                break;
        }

        $this->info('=== Proses Penarikan Data JDE Selesai ===');
        return Command::SUCCESS;
    }

    protected function outputResult(array $res): void
    {
        if ($res['success'] ?? false) {
            $this->info("[SUKSES] {$res['message']} (Sumber: " . ($res['source'] ?? 'API') . ")");
        } else {
            $this->error("[GAGAL] " . ($res['message'] ?? 'Terjadi kesalahan.'));
        }
    }
}
