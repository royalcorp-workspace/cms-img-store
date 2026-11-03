<?php

declare(strict_types=1);

namespace App\Models\Store;

use App\Traits\HasSchemaTable;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesStore extends Model
{
    use HasSchemaTable;
    use HasUuids;

    protected $table = 'sales_stores';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'sales_user_id',
        'store_id',
        'assigned_date',
        'is_primary',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function salesUser(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'sales_user_id');
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }
}
