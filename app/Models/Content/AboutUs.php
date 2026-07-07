<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasUuids;

    protected $table = 'about_us';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'company_name',
        'tagline',
        'description',
        'vision',
        'mission',
        'values',
        'established_year',
        'address',
        'phone',
        'email',
        'logo',
        'cover_image',
        'social_media',
        'is_active',
        'sort_order',
        'creator',
        'editor',
        'deleted',
    ];

    protected function casts(): array
    {
        return [
            'social_media' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'deleted' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
