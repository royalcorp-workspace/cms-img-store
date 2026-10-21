<?php

namespace App\Models\Customer;

use App\Traits\HasSchemaTable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Order\Order;

class Customer extends Model
{
    use HasSchemaTable;

    protected $table = 'customers';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'name',
        'email',
        'phone',
        'customer_type',
        'customer_code',
        'birth_date',
        'gender',
        'meta',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'meta' => 'array',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Generate format customer code: CUST.Ymd.random number (e.g., CUST.20261014.8392)
     */
    public static function generateCustomerCode(?\DateTimeInterface $date = null): string
    {
        $dateStr = ($date ? $date->format('Ymd') : now()->format('Ymd'));
        $prefix = "CUST.{$dateStr}.";

        do {
            $code = $prefix . mt_rand(1000, 9999);
        } while (static::withoutGlobalScopes()->where('customer_code', $code)->exists());

        return $code;
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->customer_code)) {
                $customer->customer_code = static::generateCustomerCode();
            }
        });

        static::addGlobalScope('active', function ($query) {
            $query->where($query->getModel()->getTable() . '.deleted', false);
        });
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'customer_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id', 'id');
    }

    public function vouchers(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Promo\Voucher::class, 'voucher_customers', 'customer_id', 'voucher_id')
            ->withPivot('creator', 'editor', 'deleted');
    }

    public function setPhoneAttribute($value): void
    {
        if ($value !== null) {
            $cleaned = preg_replace('/[\s\-\.]/', '', (string) $value);
            $this->attributes['phone'] = ltrim($cleaned, '+');
        } else {
            $this->attributes['phone'] = null;
        }
    }
}
