<?php

declare(strict_types=1);

namespace App\Models\Temporary;

use App\Traits\HasSchemaTable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TemporaryJdeCustomer extends Model
{
    use HasSchemaTable;
    use HasUuids;

    protected $table = 'store.temporary_jde_customers';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'address_number',
        'business_unit',
        'company',
        'credit_limit',
        'payment_term',
        'hold_code',
        'user_id',
        'program_id',
        'date_update',
        'time_update',
        'alpha_name',
        'channel_code',
        'channel_desc',
        'cabang_code',
        'cabang_desc',
        'sync_status',
        'mapped_store_id',
        'raw_payload',
        'notes',
    ];

    protected $casts = [
        'address_number' => 'integer',
        'credit_limit' => 'decimal:2',
        'raw_payload' => 'array',
        'date_update' => 'date',
    ];
}
