<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagRelation extends Model
{
    use HasUuids;

    protected $table = 'product_tag_relations';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'tag_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(ProductTag::class, 'tag_id');
    }
}
