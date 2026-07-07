<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class HowToReturn extends Model
{
    use HasUuids;

    protected $table = 'how_to_returns';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'slug',
        'content',
        'steps',
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
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
