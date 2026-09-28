<?php

namespace App\Models\Promo;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasUuids;

    protected $table = 'vouchers';

    protected $fillable = [
        'code',
        'title',
        'description',
        'type',
        'scope',
        'allow_stacking',
        'value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'start_date',
        'end_date',
        'valid_for_new_customer',
        'is_active',
        'show_on_web',
        'visibility',
        'store_id',
        'require_follow',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'type' => 'integer',
            'scope' => 'integer',
            'allow_stacking' => 'boolean',
            'value' => 'decimal:2',
            'min_purchase' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'usage_limit' => 'integer',
            'usage_limit_per_user' => 'integer',
            'used_count' => 'integer',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'valid_for_new_customer' => 'boolean',
            'is_active' => 'boolean',
            'show_on_web' => 'boolean',
            'require_follow' => 'boolean',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('active', function ($query) {
            $table = $query->getModel()->getTable();
            $query->where($table . '.is_active', true)
                ->where($table . '.deleted', false)
                ->where(function ($q) use ($table) {
                    $q->whereNull($table . '.start_date')->orWhere($table . '.start_date', '<=', now());
                })
                ->where(function ($q) use ($table) {
                    $q->whereNull($table . '.end_date')->orWhere($table . '.end_date', '>=', now());
                });
        });
    }

    public function scopeActive($query)
    {
        $table = $query->getModel()->getTable();
        return $query->where($table . '.is_active', true)
            ->where($table . '.deleted', false)
            ->where(function ($q) use ($table) {
                $q->whereNull($table . '.start_date')->orWhere($table . '.start_date', '<=', now());
            })
            ->where(function ($q) use ($table) {
                $q->whereNull($table . '.end_date')->orWhere($table . '.end_date', '>=', now());
            });
    }

    public function isValid(): bool
    {
        if ($this->deleted || !$this->is_active) return false;
        if ($this->start_date && $this->start_date->isFuture()) return false;
        if ($this->end_date && $this->end_date->isPast()) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        return true;
    }

    public function canBeUsedBy(?string $userId): bool
    {
        if (!$this->isValid()) return false;
        if ($this->valid_for_new_customer && $userId) return false;
        if ($this->usage_limit_per_user && $userId) {
            $userUsages = $this->usages()->where('user_id', $userId)->count();
            if ($userUsages >= $this->usage_limit_per_user) return false;
        }
        // Scope 2: Customer tertentu
        if ((int) $this->scope === 2 && $userId) {
            $hasAccess = $this->customers()
                ->where(function ($q) use ($userId) {
                    $q->where('customers.id', $userId)
                      ->orWhere('customers.user_id', $userId);
                })
                ->exists();
            if (!$hasAccess) return false;
        }
        // Scope 7: Group customer
        if ((int) $this->scope === 7 && $userId) {
            $hasAccess = $this->customerGroups()
                ->whereHas('members', function ($q) use ($userId) {
                    $q->where('customers.id', $userId)
                      ->orWhere('customers.user_id', $userId);
                })
                ->exists();
            if (!$hasAccess) return false;
        }
        return true;
    }

    public function isStackable(): bool
    {
        // Hanya voucher diskon ongkir (type = 3) yang dapat di-stack
        return (int) $this->type === 3 && (bool) $this->allow_stacking;
    }

    /**
     * Memeriksa apakah voucher ini dapat digabungkan dengan voucher lain.
     * Aturan: Hanya voucher diskon ongkir (type = 3 dan allow_stacking = true)
     * yang dapat digabungkan dengan 1 voucher diskon belanja lainnya (persen/nominal).
     * Dua voucher non-ongkir (misal Persen + Nominal, Persen + Persen) tidak dapat digabungkan.
     */
    public function canBeStackedWith(Voucher $other): bool
    {
        if (!empty($this->id) && !empty($other->id) && $this->id === $other->id) {
            return false;
        }

        $isThisShipping = (int) $this->type === 3;
        $isOtherShipping = (int) $other->type === 3;

        // Jika kedua voucher bukan diskon ongkir, tidak dapat digabungkan
        if (!$isThisShipping && !$isOtherShipping) {
            return false;
        }

        // Jika kedua voucher adalah diskon ongkir, tidak dapat digabungkan
        if ($isThisShipping && $isOtherShipping) {
            return false;
        }

        // Voucher diskon ongkir harus mengizinkan stacking
        if ($isThisShipping && !$this->isStackable()) {
            return false;
        }
        if ($isOtherShipping && !$other->isStackable()) {
            return false;
        }

        return true;
    }

    /**
     * Memvalidasi kombinasi beberapa voucher.
     * Maksimal 1 voucher diskon belanja (persen/nominal) + maksimal 1 voucher diskon ongkir yang stackable.
     *
     * @param iterable<Voucher> $vouchers
     * @return bool
     */
    public static function validateVoucherCombination(iterable $vouchers): bool
    {
        $shippingCount = 0;
        $nonShippingCount = 0;

        foreach ($vouchers as $voucher) {
            if ((int) $voucher->type === 3) {
                if (!$voucher->isStackable()) {
                    return false;
                }
                $shippingCount++;
            } else {
                $nonShippingCount++;
            }
        }

        return $shippingCount <= 1 && $nonShippingCount <= 1;
    }

    /**
     * Scope labels mapped to scope integer values.
     *
     * 1 = Semua Customer (Voucher Toko)
     * 2 = Customer Tertentu (Voucher Terbatas)
     * 3 = Kategori Tertentu
     * 4 = Produk Tertentu (per Artikel)
     * 5 = Brand Tertentu
     * 6 = Brand + Artikel
     * 7 = Group Customer (Pembeli Terpilih)
     */
    public function scopeLabel(): string
    {
        return match ((int) $this->scope) {
            2 => 'Customer Tertentu',
            3 => 'Kategori Tertentu',
            4 => 'Produk Tertentu',
            5 => 'Brand Tertentu',
            6 => 'Brand + Artikel',
            7 => 'Group Customer',
            default => 'Semua Produk Aktif (Voucher Toko / Web)',
        };
    }

    public function discountLabel(): string
    {
        return match ((int) $this->type) {
            1 => 'Persentase (%)',
            2 => 'Nominal (Rp)',
            3 => 'Diskon Ongkir (Rp)',
            4 => 'Bonus Produk (pcs)',
            default => 'Tidak diketahui',
        };
    }

    public function visibilityLabel(): string
    {
        return match ($this->visibility ?? 'public') {
            'claimable' => 'Harus Diklaim',
            'hidden' => 'Tersembunyi (Kode)',
            default => 'Publik',
        };
    }

    // ─── Relationships ────────────────────────────────────

    public function store(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Store\Store::class, 'store_id', 'id');
    }

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Customer\Customer::class, 'voucher_customers', 'voucher_id', 'customer_id')
            ->withPivot('creator', 'editor', 'deleted');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Product\Category::class, 'voucher_categories', 'voucher_id', 'category_id')
            ->wherePivot('deleted', false)
            ->where('product_category.deleted', false)
            ->withPivot('creator', 'editor', 'deleted');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Product\Product::class, 'voucher_products', 'voucher_id', 'product_id')
            ->wherePivot('deleted', false)
            ->withPivot('creator', 'editor', 'deleted');
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Product\Brand::class, 'voucher_brands', 'voucher_id', 'brand_id')
            ->wherePivot('deleted', false)
            ->withPivot('creator', 'editor', 'deleted');
    }

    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Customer\CustomerGroup::class, 'voucher_customer_groups', 'voucher_id', 'customer_group_id')
            ->wherePivot('deleted', false)
            ->withPivot('creator', 'editor', 'deleted');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(VoucherClaim::class, 'voucher_id', 'id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(VoucherUsage::class, 'voucher_id', 'id');
    }
}
