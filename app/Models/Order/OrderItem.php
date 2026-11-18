<?php

namespace App\Models\Order;

use App\Traits\HasSchemaTable;

use App\Models\Order\Order;
use App\Models\Product\Product;
use App\Models\Product\Variant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasSchemaTable;

    use HasUuids;

    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'name',
        'quantity',
        'unit_price',
        'dpp_price',
        'ppn_rate',
        'ppn_amount',
        'total',
        'discount_nominal',
        'discount_percent',
        'item_notes',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'dpp_price' => 'decimal:2',
            'ppn_rate' => 'decimal:2',
            'ppn_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'discount_nominal' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'meta' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'product_variant_id', 'id');
    }

    public function pickingListItem(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Picking\PickingListItem::class, 'order_item_id');
    }

    public function packingSlipItem(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\Packing\PackingSlipItem::class, 'order_item_id');
    }

    /**
     * Dapatkan 2nd_item_number untuk JDE (hanya ambil base SKU murni sebelum dash/suffix)
     * Contoh: KBR100010011348S200120-FS-T20 -> KBR100010011348S200120
     */
    public function getJdeItemNumber(): string
    {
        $raw = $this->variant?->sku
            ?? ($this->meta['sku'] ?? null)
            ?? $this->product?->code
            ?? $this->name;

        $clean = trim((string) $raw);
        if (str_contains($clean, '-')) {
            $clean = explode('-', $clean)[0];
        }

        return $clean;
    }

    /**
     * Dapatkan harga satuan setelah diskon per unit/SKU (Net Price per SKU, bukan total sales).
     */
    public function getPriceAfterDiscount(): float
    {
        $qty = max(1, (int) $this->quantity);

        // 1. Cek meta['after_disc_price'] (harga satuan setelah diskon per SKU)
        if (isset($this->meta['after_disc_price']) && is_numeric($this->meta['after_disc_price'])) {
            $metaPrice = (float) $this->meta['after_disc_price'];
            $unitPrice = (float) ($this->unit_price ?? 0);

            // Pengaman: jika metaPrice melebihi unitPrice saat qty > 1, berarti tersimpan subtotal
            if ($unitPrice > 0 && $metaPrice > $unitPrice && $qty > 1) {
                return (float) ($metaPrice / $qty);
            }
            return $metaPrice;
        }

        // 2. Jika ada total baris, hitung harga satuan per SKU = total / qty
        if (!empty($this->total) && (float) $this->total > 0) {
            return (float) ($this->total / $qty);
        }

        // 3. Fallback: unit_price - (discount_nominal / qty)
        $unitPrice = (float) ($this->unit_price ?? 0);
        $discount = (float) ($this->discount_nominal ?? 0);

        return max(0, $unitPrice - ($discount / $qty));
    }

    /**
     * Dapatkan status aktif PPN item
     */
    public function getIsPpnActiveAttribute(): bool
    {
        if (isset($this->attributes['is_ppn_active'])) {
            return (bool) $this->attributes['is_ppn_active'];
        }
        if (isset($this->meta['is_ppn_active'])) {
            return (bool) $this->meta['is_ppn_active'];
        }
        return (float) ($this->attributes['ppn_rate'] ?? 0) > 0;
    }

    /**
     * Dapatkan tarif PPN item secara dinamis
     */
    public function getPpnRateAttribute($value): float
    {
        if (isset($this->attributes['is_ppn_active']) && !$this->attributes['is_ppn_active']) {
            return 0.0;
        }

        if ($value !== null && $value !== '') {
            return (float) $value;
        }

        return (float) config('services.tax.ppn_rate', config('app.ppn_rate', env('PPN_RATE', 11.0)));
    }

    /**
     * Dapatkan harga satuan sebelum PPN (DPP)
     */
    public function getDppPriceAttribute($value): float
    {
        if (!empty($value) && (float) $value > 0) {
            return (float) $value;
        }

        $unitPrice = (float) ($this->unit_price ?? 0);
        if ($unitPrice <= 0) {
            return 0.0;
        }

        $rate = (float) $this->ppn_rate;
        if ($rate <= 0) {
            return $unitPrice;
        }

        return round($unitPrice / (1 + ($rate / 100)), 2);
    }

    /**
     * Dapatkan nominal PPN harga satuan
     */
    public function getPpnAmountAttribute($value): float
    {
        if (!empty($value) && (float) $value > 0) {
            return (float) $value;
        }

        $unitPrice = (float) ($this->unit_price ?? 0);
        if ($unitPrice <= 0) {
            return 0.0;
        }

        $rate = (float) $this->ppn_rate;
        if ($rate <= 0) {
            return 0.0;
        }

        return round($unitPrice - $this->dpp_price, 2);
    }

    /**
     * Dapatkan alokasi diskon voucher produk untuk item ini (jika order memiliki voucher diskon produk).
     * Mengikuti formula Excel "Cara Hitung PPN Website.xlsx" (Sub Total = Harga Produk - Diskon - Voucher).
     */
    public function getVoucherDiscountShare(): float
    {
        $order = $this->order;
        if (!$order) {
            return 0.0;
        }

        $orderProductVoucher = (float) ($order->product_voucher_discount ?? 0);
        if ($orderProductVoucher <= 0) {
            return 0.0;
        }

        // Hitung total nilai seluruh item setelah diskon promo di order ini
        $totalItemsAfterPromo = 0.0;
        if ($order->relationLoaded('items') || $order->items) {
            foreach ($order->items as $it) {
                $itQty = max(1, (int) $it->quantity);
                $itBase = (float) ($it->unit_price ?? 0) * $itQty;
                $itPromoDisc = (float) ($it->discount_nominal ?? 0);
                $itSub = max(0.0, (float) (!empty($it->total) ? $it->total : ($itBase - $itPromoDisc)));
                $totalItemsAfterPromo += $itSub;
            }
        }

        if ($totalItemsAfterPromo <= 0) {
            return 0.0;
        }

        $myQty = max(1, (int) $this->quantity);
        $myBase = (float) ($this->unit_price ?? 0) * $myQty;
        $myPromoDisc = (float) ($this->discount_nominal ?? 0);
        $mySub = max(0.0, (float) (!empty($this->total) ? $this->total : ($myBase - $myPromoDisc)));

        return round($orderProductVoucher * ($mySub / $totalItemsAfterPromo), 2);
    }

    /**
     * Dapatkan total nilai bersih item ini (setelah diskon promo dan voucher produk).
     * Mengikuti formula Excel "Cara Hitung PPN Website.xlsx" (Sub Total = Harga Produk - Diskon - Voucher).
     */
    public function getNetTotal(): float
    {
        $qty = max(1, (int) $this->quantity);
        $myBase = (float) ($this->unit_price ?? 0) * $qty;
        $myPromoDisc = (float) ($this->discount_nominal ?? 0);
        $mySub = max(0.0, (float) (!empty($this->total) ? $this->total : ($myBase - $myPromoDisc)));
        $voucherShare = $this->getVoucherDiscountShare();

        return max(0.0, round($mySub - $voucherShare, 2));
    }

    /**
     * Dapatkan harga satuan bersih (setelah diskon & voucher).
     */
    public function getNetUnitPrice(): float
    {
        $qty = max(1, (int) $this->quantity);
        return round($this->getNetTotal() / $qty, 2);
    }

    /**
     * Dapatkan harga satuan setelah seluruh diskon TANPA PAJAK (DPP untuk push ke JDE).
     * Mengikuti formula Excel "Cara Hitung PPN Website.xlsx": DPP = Sub Total / 1.11
     */
    public function getPriceWithoutTax(?float $ppnRate = null): float
    {
        $rate = $ppnRate ?? $this->ppn_rate;
        $qty = max(1, (int) $this->quantity);
        $dppTotal = $this->getDppTotal($rate);
        return round($dppTotal / $qty, 2);
    }

    /**
     * Dapatkan total DPP untuk seluruh kuantiti item ini (setelah diskon promo & voucher).
     * Formula Excel: DPP = Sub Total / (1 + (ppn_rate / 100))
     */
    public function getDppTotal(?float $ppnRate = null): float
    {
        $rate = $ppnRate ?? $this->ppn_rate;
        if ($rate <= 0) {
            return $this->getNetTotal();
        }
        return round($this->getNetTotal() / (1 + ($rate / 100)), 2);
    }

    /**
     * Dapatkan total PPN untuk seluruh kuantiti item ini (setelah diskon promo & voucher).
     * Formula Excel: PPN = DPP * (ppn_rate / 100) atau Sub Total - DPP
     */
    public function getPpnTotal(?float $ppnRate = null): float
    {
        $netTotal = $this->getNetTotal();
        return round($netTotal - $this->getDppTotal($ppnRate), 2);
    }
}
