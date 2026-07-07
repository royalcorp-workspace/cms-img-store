<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PrivacyPolicy extends Model
{
    use HasUuids;

    protected $table = 'privacy_policies';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'title',
        'slug',
        'content',
        'version',
        'effective_date',
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
            'effective_date' => 'date',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
