<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasUuids;

    protected $table = 'banners';

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'link_url',
        'is_active',
        'sort_order',
        'type',
        'device_flag',
        'placement_size',
        'content_type',
        'image_web_url',
        'image_mobile_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'type' => 'integer',
        'device_flag' => 'integer',
        'placement_size' => 'integer',
        'content_type' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function images()
    {
        return $this->hasMany(BannerImage::class)->where('deleted', false)->orderBy('sort_order');
    }
}
