<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadStage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'color',
        'order_index',
        'is_system'
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class, 'stage_id');
    }
}
