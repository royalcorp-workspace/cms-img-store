<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasUuids;

    protected $table = 'faqs';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'question',
        'answer',
        'sort_order',
        'is_published',
        'view_count',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
            'view_count' => 'integer',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
