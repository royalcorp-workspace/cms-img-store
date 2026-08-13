<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignLog extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'campaign_id',
        'customer_id',
        'email',
        'status',
        'error_message',
        'sent_at'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
