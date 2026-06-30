<?php

namespace App\Models\Product;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Image extends Model
{
    use HasUuids;

    protected $table = 'product_images';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'product_id',
        'image',
        'alt_text',
        'sort_order',
        'status',
    ];

    protected $appends = ['url'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'status' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function getUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
