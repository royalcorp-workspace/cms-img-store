<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Store\Store;
use App\Models\Store\StoreGroup;
use App\Models\Store\StoreChannel;
use App\Models\Customer\Customer;
use App\Models\Order\Order;

class SyncJdeMasterDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jde:sync-master 
                            {--cust-file= : Path ke file middle-cust.json}
                            {--branch-file= : Path ke file middle-branch.json}
                            {--force : Jalankan sinkronisasi dan pembersihan data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pembersihan dan sinkronisasi master data Branch, Store, Channel, dan Customer dari middle-branch & middle-cust JSON';

    public function handle()
    {
        $this->info('=== Memulai Pembersihan & Sinkronisasi Master Data JDE ===');

        $middleBranchPath = $this->option('branch-file') ?: storage_path('data/middle-branch.json');
        if (!file_exists($middleBranchPath)) {
            $middleBranchPath = storage_path('app/data/middle-branch.json');
            if (!file_exists($middleBranchPath)) {
                $middleBranchPath = base_path('../docs/middle-branch.json');
                if (!file_exists($middleBranchPath)) {
                    $middleBranchPath = '/home/ras210201083312/Documents/royal/docs/middle-branch.json';
                }
            }
        }

        $middleCustPath = $this->option('cust-file') ?: storage_path('data/middle-cust.json');
        if (!file_exists($middleCustPath)) {
            $middleCustPath = storage_path('app/data/middle-cust.json');
            if (!file_exists($middleCustPath)) {
                $middleCustPath = base_path('../docs/middle-cust.json');
                if (!file_exists($middleCustPath)) {
                    $middleCustPath = '/home/ras210201083312/Documents/royal/docs/middle-cust.json';
                }
            }
        }

        $custMap = [];
        if (file_exists($middleCustPath)) {
            $this->info("Membaca data customer dari {$middleCustPath}...");
            $custRaw = json_decode(file_get_contents($middleCustPath), true);
            $custData = $custRaw['data'] ?? [];

            foreach ($custData as $c) {
                $bu = trim((string)($c['business_unit'] ?? ''));
                $an = (int)($c['address_number'] ?? 0);
                if ($bu && $an && !isset($custMap[$bu])) {
                    $custMap[$bu] = [
                        'address_number' => $an,
                        'name' => trim((string)($c['alpha_name'] ?? '')),
                        'cabang_desc' => trim((string)($c['cabang_desc'] ?? '')),
                        'channel_desc' => trim((string)($c['channel_desc'] ?? '')),
                    ];
                }
            }
            $this->info("Berhasil memetakan " . count($custMap) . " business unit dari middle-cust.");
        } else {
            $this->warn("File middle-cust.json tidak ditemukan.");
        }

        // 1. Buat Store dan Channel untuk 11001006
        $this->info('1. Memastikan Store dan Channel 11001006...');
        $storeGroup11001006 = StoreGroup::firstOrCreate(
            ['code' => '11001006'],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Group 11001006',
                'status' => true,
                'sort_order' => 0,
                'deleted' => false,
            ]
        );

        $defaultTierId = DB::table('store_tier')->value('id') ?? '0e9f878a-ef16-404d-bdf1-21c1b023a06c';
        $defaultChannelGroupId = DB::table('store_channel_group')->where('code', 'DEFAULT')->value('id')
            ?? DB::table('store_channel_group')->value('id');

        $store11001006 = Store::where('code', '11001006')->first();
        if (!$store11001006) {
            $store11001006 = Store::create([
                'id' => (string) Str::uuid(),
                'store_group_id' => $storeGroup11001006->id,
                'tier_id' => $defaultTierId,
                'code' => '11001006',
                'address_number' => 100228,
                'name' => 'Store 11001006',
                'credit_limit' => 0,
                'outstanding_balance' => 0,
                'payment_term' => 0,
                'status' => true,
                'sort_order' => 0,
                'deleted' => false,
            ]);
            $this->info("-> Store 11001006 berhasil dibuat.");
        } else {
            if (!$store11001006->address_number) {
                $store11001006->address_number = 100228;
                $store11001006->save();
            }
            $this->info("-> Store 11001006 sudah ada.");
        }

        $ch11001006 = StoreChannel::where('code', '11001006')->first();
        if (!$ch11001006) {
            StoreChannel::create([
                'id' => (string) Str::uuid(),
                'store_id' => $store11001006->id,
                'store_channel_group_id' => $defaultChannelGroupId,
                'code' => '11001006',
                'name' => 'Channel 11001006',
                'status' => true,
                'sort_order' => 0,
                'deleted' => false,
            ]);
            $this->info("-> StoreChannel 11001006 berhasil dibuat.");
        } else {
            $this->info("-> StoreChannel 11001006 sudah ada.");
        }

        // 2. Pembersihan Store Channel (hanya ambil kode business_unit murni tanpa sufiks program)
        $this->info('2. Membersihkan Store Channels (menghapus format -PROGRAMID dan menyesuaikan nama)...');
        $allStores = Store::all();
        $cleanedCount = 0;

        foreach ($allStores as $st) {
            // Update address_number store jika belum ada dan ada di custMap
            if (!$st->address_number && isset($custMap[$st->code])) {
                $st->address_number = $custMap[$st->code]['address_number'];
                $st->save();
            }

            // Ambil semua channels milik store ini
            $channels = StoreChannel::where('store_id', $st->id)->get();
            if ($channels->isEmpty()) {
                continue;
            }

            // Target code murni = store code (misal 1801103, 18011, dsb.)
            $targetCode = $st->code;
            $targetName = $st->name ?: $targetCode;

            // Cari apakah sudah ada channel dengan targetCode murni
            $primaryChannel = $channels->firstWhere('code', $targetCode);

            if (!$primaryChannel) {
                // Pilih channel pertama untuk diubah kodenya menjadi targetCode murni
                $primaryChannel = $channels->first();
                $primaryChannel->code = $targetCode;
                $primaryChannel->name = $targetName;
                $primaryChannel->save();
                $cleanedCount++;
            } else {
                $primaryChannel->name = $targetName;
                $primaryChannel->save();
            }

            // Hapus duplikat channel lain untuk store yang sama
            foreach ($channels as $otherCh) {
                if ($otherCh->id !== $primaryChannel->id) {
                    $otherCh->delete();
                }
            }
        }
        $this->info("-> Selesai merapikan channels. {$cleanedCount} channel diperbarui.");

        // 3. Update data customer
        $this->info('3. Menyesuaikan format customer_code (CUST.Ymd.random number) pada tabel customers...');
        $custUpdated = 0;
        $customers = Customer::all();
        foreach ($customers as $c) {
            $updated = false;
            if (!$c->customer_code || !str_starts_with($c->customer_code, 'CUST.')) {
                $c->customer_code = Customer::generateCustomerCode($c->created_at);
                $updated = true;
            }
            if ($updated) {
                $c->save();
                $custUpdated++;
            }
        }
        $this->info("-> {$custUpdated} customer berhasil disinkronkan customer_code nya.");

        // 4. Update order yang sudah ada dengan store_id, business_unit, dan address_number
        $this->info('4. Menghubungkan orders yang ada ke Store & Channel...');
        $defaultStore = Store::where('code', '18011')->orWhere('code', '11001006')->first() ?? $store11001006;
        $defaultChannel = StoreChannel::where('store_id', $defaultStore->id)->first() ?? $ch11001006;

        $orders = Order::all();
        $orderUpdated = 0;
        foreach ($orders as $ord) {
            $ordUpdated = false;
            if (!$ord->store_id) {
                $ord->store_id = $defaultStore->id;
                $ordUpdated = true;
            }
            if (!$ord->store_channel_id && $defaultChannel) {
                $ord->store_channel_id = $defaultChannel->id;
                $ordUpdated = true;
            }
            if (!$ord->business_unit) {
                $ord->business_unit = $defaultStore->code;
                $ordUpdated = true;
            }
            if (!$ord->address_number) {
                $ord->address_number = $defaultStore->address_number ?: 100228;
                $ordUpdated = true;
            }
            if ($ordUpdated) {
                $ord->save();
                $orderUpdated++;
            }
        }
        $this->info("-> {$orderUpdated} order berhasil diperbarui relasi store & channel-nya.");

        $this->info('=== Sinkronisasi & Pembersihan Master JDE Selesai ===');
        return Command::SUCCESS;
    }
}
