<?php

namespace App\Models\Content;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BannerImage extends Model
{
    use HasUuids;

    protected $table = 'banner_images';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'banner_id',
        'image_web_url',
        'image_mobile_url',
        'link_url',
        'sort_order',
        'deleted',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'deleted'    => 'boolean',
    ];

    public function banner()
    {
        return $this->belongsTo(Banner::class);
    }
}
