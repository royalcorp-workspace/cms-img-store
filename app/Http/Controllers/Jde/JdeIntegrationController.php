<?php

declare(strict_types=1);

namespace App\Http\Controllers\Jde;

use App\Http\Controllers\Controller;
use App\Models\Temporary\TemporaryJdeItemMaster;
use App\Models\Temporary\TemporaryJdeBasePrice;
use App\Models\Temporary\TemporaryJdeBranch;
use App\Models\Temporary\TemporaryJdeCustomer;
use App\Services\JdePullService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class JdeIntegrationController extends Controller
{
    public function __construct(
        protected JdePullService $jdePullService
    ) {}

    /**
     * Halaman Dashboard Integrasi JDE & Data Staging
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'item_master'); // item_master, base_price, branch, customer
        $search = $request->query('search');
        $brand = $request->query('brand');

        // Daftar Brand Unik untuk filter
        $brands = TemporaryJdeItemMaster::query()
            ->whereNotNull('sales_code1_desc')
            ->whereRaw("TRIM(sales_code1_desc) != ''")
            ->where('sales_code1_desc', '!=', 'NON-BRAND')
            ->distinct()
            ->orderBy('sales_code1_desc')
            ->pluck('sales_code1_desc');

        // Statistik Ringkasan
        $stats = [
            'item_masters_total' => TemporaryJdeItemMaster::count(),
            'item_masters_pending' => TemporaryJdeItemMaster::where('sync_status', 'PENDING')->count(),
            'item_masters_branded' => TemporaryJdeItemMaster::whereNotNull('sales_code1_desc')
                ->whereRaw("TRIM(sales_code1_desc) != ''")
                ->where('sales_code1_desc', '!=', 'NON-BRAND')
                ->count(),
            'base_prices_total' => TemporaryJdeBasePrice::count(),
            'branches_total' => TemporaryJdeBranch::count(),
            'customers_total' => TemporaryJdeCustomer::count(),
            'last_sync_item' => TemporaryJdeItemMaster::latest('updated_at')->value('updated_at'),
            'last_sync_price' => TemporaryJdeBasePrice::latest('updated_at')->value('updated_at'),
            'last_sync_branch' => TemporaryJdeBranch::latest('updated_at')->value('updated_at'),
            'last_sync_customer' => TemporaryJdeCustomer::latest('updated_at')->value('updated_at'),
        ];

        $data = match ($tab) {
            'base_price' => $this->getBasePricesQuery($search),
            'branch' => $this->getBranchesQuery($search),
            'customer' => $this->getCustomersQuery($search),
            default => $this->getItemMastersQuery($search, $brand),
        };

        return view('pages.jde.index', compact('tab', 'stats', 'data', 'search', 'brand', 'brands'));
    }

    /**
     * Trigger Manual Penarikan Data JDE (API / On-Demand)
     */
    public function pull(Request $request): JsonResponse
    {
        $type = $request->input('type', 'item_master');
        $limit = (int) $request->input('limit', 0);

        try {
            $result = match ($type) {
                'item_master' => $this->jdePullService->pullItemMasters(null, $limit),
                'base_price' => $this->jdePullService->pullBasePrices(null, $limit),
                'branch' => $this->jdePullService->pullBranches(null, $limit),
                'customer' => $this->jdePullService->pullCustomers(null, $limit),
                default => $this->jdePullService->pullAll($limit),
            };

            $isSuccess = $result['success'] ?? true;

            return response()->json([
                'success' => $isSuccess,
                'type' => $type,
                'data' => $result,
                'message' => $result['message'] ?? ($isSuccess ? 'Penarikan data JDE berhasil diproses.' : 'Gagal memproses penarikan data JDE.'),
            ], $isSuccess ? 200 : 400);
        } catch (\Throwable $e) {
            Log::channel('jde')->error('Manual JDE Pull Exception', [
                'type' => $type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menarik data JDE: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload File Dump JSON JDE secara manual
     */
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:51200', // Max 50MB
            'type' => 'required|string|in:item_master,base_price,branch,customer',
        ]);

        $type = $request->input('type');
        $uploadedFile = $request->file('file');
        $path = $uploadedFile->storeAs('tmp/jde', $uploadedFile->getClientOriginalName());
        $fullPath = storage_path('app/' . $path);

        try {
            $result = match ($type) {
                'item_master' => $this->jdePullService->pullItemMasters($fullPath),
                'base_price' => $this->jdePullService->pullBasePrices($fullPath),
                'branch' => $this->jdePullService->pullBranches($fullPath),
                'customer' => $this->jdePullService->pullCustomers($fullPath),
            };

            return response()->json([
                'success' => true,
                'type' => $type,
                'data' => $result,
                'message' => "File {$uploadedFile->getClientOriginalName()} berhasil diproses: " . ($result['message'] ?? ''),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses file upload: ' . $e->getMessage(),
            ], 500);
        }
    }

    protected function getItemMastersQuery(?string $search, ?string $brand = null)
    {
        $query = TemporaryJdeItemMaster::query();

        // Filter Brand
        if ($brand === 'has_brand') {
            $query->whereNotNull('sales_code1_desc')
                ->whereRaw("TRIM(sales_code1_desc) != ''")
                ->where('sales_code1_desc', '!=', 'NON-BRAND');
        } elseif ($brand === 'no_brand') {
            $query->where(function ($q) {
                $q->whereNull('sales_code1_desc')
                  ->orWhereRaw("TRIM(sales_code1_desc) = ''")
                  ->orWhere('sales_code1_desc', 'NON-BRAND');
            });
        } elseif (!empty($brand)) {
            $query->where('sales_code1_desc', $brand);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('second_item_number', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
                  ->orWhere('sales_code1_desc', 'ilike', "%{$search}%");
            });
        }

        // Urutkan data dari yang memiliki brand terlebih dahulu, kemudian alfabetis brand & SKU
        $query->orderByRaw("
            CASE 
                WHEN sales_code1_desc IS NOT NULL AND TRIM(sales_code1_desc) != '' AND sales_code1_desc != 'NON-BRAND' THEN 0 
                ELSE 1 
            END ASC
        ")
        ->orderBy('sales_code1_desc', 'asc')
        ->orderBy('second_item_number', 'asc');

        return $query->paginate(15)->withQueryString();
    }

    protected function getBasePricesQuery(?string $search)
    {
        $query = TemporaryJdeBasePrice::query()->latest('updated_at');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('second_item_number', 'ilike', "%{$search}%")
                  ->orWhere('business_unit', 'ilike', "%{$search}%")
                  ->orWhere('business_unit_desc', 'ilike', "%{$search}%");
            });
        }
        return $query->paginate(15)->withQueryString();
    }

    protected function getBranchesQuery(?string $search)
    {
        $query = TemporaryJdeBranch::query()->latest('updated_at');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('business_unit', 'ilike', "%{$search}%")
                  ->orWhere('cabang_desc', 'ilike', "%{$search}%")
                  ->orWhere('cabang_code', 'ilike', "%{$search}%");
            });
        }
        return $query->paginate(15)->withQueryString();
    }

    protected function getCustomersQuery(?string $search)
    {
        $query = TemporaryJdeCustomer::query()->latest('updated_at');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('alpha_name', 'ilike', "%{$search}%")
                  ->orWhere('business_unit', 'ilike', "%{$search}%")
                  ->orWhere('cabang_desc', 'ilike', "%{$search}%")
                  ->orWhere('address_number', 'like', "%{$search}%");
            });
        }
        return $query->paginate(15)->withQueryString();
    }
}
