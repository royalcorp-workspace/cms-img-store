<?php

declare(strict_types=1);

namespace App\Models\Temporary;

use App\Traits\HasSchemaTable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class TemporaryJdeItemMaster extends Model
{
    use HasSchemaTable;
    use HasUuids;

    protected $table = 'finish_goods.temporary_jde_item_masters';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'short_item_no',
        'second_item_number',
        'description',
        'description_line_2',
        'sales_code1',
        'sales_code1_desc',
        'process2',
        'uom',
        'stocking_type',
        'line_type',
        'user_id',
        'program_id',
        'date_update',
        'template',
        'segments',
        'post_retail',
        'img_reference',
        'tax_type',
        'sync_status',
        'processed_product_id',
        'processed_by',
        'processed_at',
        'raw_payload',
        'error_message',
    ];

    protected $casts = [
        'short_item_no' => 'integer',
        'segments' => 'array',
        'raw_payload' => 'array',
        'date_update' => 'date',
        'processed_at' => 'datetime',
    ];
}
