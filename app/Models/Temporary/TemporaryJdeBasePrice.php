<?php

declare(strict_types=1);

namespace App\Models\Temporary;

use App\Traits\HasSchemaTable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TemporaryJdeBasePrice extends Model
{
    use HasSchemaTable;
    use HasUuids;

    protected $table = 'finish_goods.temporary_jde_base_prices';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'short_item_no',
        'second_item_number',
        'business_unit',
        'business_unit_desc',
        'currency_code',
        'uom',
        'base_price',
        'effective_date',
        'expired_date',
        'user_id',
        'program_id',
        'date_update',
        'time_of_day',
        'tax_included',
        'sync_status',
        'mapped_price_id',
        'mapped_warehouse_id',
        'processed_by',
        'processed_at',
        'raw_payload',
        'error_message',
    ];

    protected $casts = [
        'short_item_no' => 'integer',
        'base_price' => 'decimal:2',
        'tax_included' => 'boolean',
        'raw_payload' => 'array',
        'effective_date' => 'date',
        'expired_date' => 'date',
        'date_update' => 'date',
        'processed_at' => 'datetime',
    ];
}
