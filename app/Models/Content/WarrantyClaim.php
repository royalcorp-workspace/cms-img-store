<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WarrantyClaim extends Model
{
    use HasUuids;

    protected $table = 'warranty_claims';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'slug',
        'content',
        'steps',
        'required_documents',
        'processing_time_days',
        'featured_image',
        'is_published',
        'sort_order',
        'meta_title',
        'meta_description',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'steps' => 'array',
            'required_documents' => 'array',
            'processing_time_days' => 'integer',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
