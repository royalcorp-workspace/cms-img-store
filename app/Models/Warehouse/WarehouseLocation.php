<?php

namespace App\Models\Warehouse;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseLocation extends Model
{
    use HasUuids;

    protected $fillable = ['warehouse_id', 'code', 'name', 'type', 'parent_id'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'updated_at' => 'datetime'];
    }

    public function getTypeAttribute($value)
    {
        $map = [
            1 => 'zone',
            2 => 'area',
            3 => 'rack',
            4 => 'shelf',
            5 => 'bin',
        ];
        return $map[$value] ?? 'zone';
    }

    public function setTypeAttribute($value)
    {
        $map = [
            'zone' => 1,
            'area' => 2,
            'rack' => 3,
            'shelf' => 4,
            'bin' => 5,
        ];
        $this->attributes['type'] = $map[$value] ?? 1;
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class, 'parent_id');
    }
}