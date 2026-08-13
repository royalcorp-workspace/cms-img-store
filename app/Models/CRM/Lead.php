<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lead extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'customer_id',
        'stage_id',
        'amount',
        'notes',
        'lost_reason'
    ];

    public function stage()
    {
        return $this->belongsTo(LeadStage::class, 'stage_id');
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer\Customer::class, 'customer_id');
    }
}
