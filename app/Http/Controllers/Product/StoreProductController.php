<?php

declare(strict_types=1);

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product\StoreProduct;
use App\Models\Store\SalesStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreProductController extends Controller
{
    /**
     * Update custom retail price, floor display status, or POS active flag.
     * Accessible by: Toko Owner, Sales Canvasser pembina toko, & Super Admin.
     */
    public function update(Request $request, string $id)
    {
        $storeProduct = StoreProduct::findOrFail($id);
        $user = auth('admin')->user();

        // Verifikasi Otorisasi:
        // 1. Super Admin: full access
        // 2. Sales Canvasser: wajib membina store_id produk ini di sales_stores
        // 3. Store: wajib store_id yang sama dengan akun login
        if ($user && method_exists($user, 'isSales') && $user->isSales()) {
            $isAssigned = SalesStore::where('sales_user_id', $user->id)
                ->where('store_id', $storeProduct->store_id)
                ->exists();

            if (!$isAssigned) {
                return back()->with('error', 'Anda tidak memiliki otorisasi untuk mengubah produk pada toko ini.');
            }
        } elseif ($user && method_exists($user, 'isStore') && $user->isStore()) {
            $currentStoreId = session('current_store.id') ?? $user->store_id;
            if ($storeProduct->store_id !== $currentStoreId) {
                return back()->with('error', 'Anda hanya berhak mengelola produk milik toko Anda.');
            }
        }

        $validated = $request->validate([
            'custom_retail_price' => 'nullable|numeric|min:0',
            'is_floor_display'    => 'nullable|boolean',
            'is_active_for_pos'   => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($storeProduct, $validated, $request) {
            if ($request->has('custom_retail_price')) {
                $storeProduct->custom_retail_price = $validated['custom_retail_price'];
            }
            if ($request->has('is_floor_display')) {
                $storeProduct->is_floor_display = $request->boolean('is_floor_display');
            }
            if ($request->has('is_active_for_pos')) {
                $storeProduct->is_active_for_pos = $request->boolean('is_active_for_pos');
            }
            $storeProduct->save();
        });

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Pengaturan produk {$storeProduct->item_name} berhasil disimpan.",
                'data'    => $storeProduct,
            ]);
        }

        return back()->with('success', "Harga jual & display {$storeProduct->item_name} berhasil diperbarui.");
    }
}
