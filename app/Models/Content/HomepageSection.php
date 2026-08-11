<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class HomepageSection extends Model
{
    protected $table = 'homepage_sections';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'section_key',
        'title',
        'sort_order',
        'is_visible',
        'meta'
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'meta' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}
