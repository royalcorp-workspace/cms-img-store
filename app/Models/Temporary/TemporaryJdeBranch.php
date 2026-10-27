<?php

declare(strict_types=1);

namespace App\Models\Temporary;

use App\Traits\HasSchemaTable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TemporaryJdeBranch extends Model
{
    use HasSchemaTable;
    use HasUuids;

    protected $table = 'store.temporary_jde_branches';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'business_unit',
        'company',
        'cabang_code',
        'cabang_desc',
        'short_item_no',
        'process2',
        'stocking_type',
        'user_id',
        'program_id',
        'date_update',
        'time_of_day',
        'sync_status',
        'mapped_branch_id',
        'mapped_warehouse_id',
        'raw_payload',
    ];

    protected $casts = [
        'short_item_no' => 'integer',
        'raw_payload' => 'array',
        'date_update' => 'date',
    ];
}
