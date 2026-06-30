<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'product_id',
        'order_id',
        'user_name',
        'user_email',
        'rating',
        'text',
        'image_url',
        'is_approved',
        'is_published',
        'report_count',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_approved' => 'boolean',
            'is_published' => 'boolean',
            'report_count' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope('published', function ($query) {
            $query->where('is_published', true)
                ->where('is_approved', true);
        });
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->where('is_approved', true);
    }

    public function scopeWithReports($query)
    {
        return $query->where('report_count', '>', 0);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Order\Order::class, 'order_id');
    }
}
