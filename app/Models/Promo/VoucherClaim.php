<?php

namespace App\Models\Promo;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class VoucherClaim extends Model
{
    use HasUuids;

    protected $table = 'voucher_claims';

    protected $fillable = [
        'voucher_id',
        'customer_id',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'voucher_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class, 'customer_id', 'id');
    }
}
