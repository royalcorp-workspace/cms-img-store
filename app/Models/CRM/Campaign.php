<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'subject',
        'email_template_id',
        'status',
        'scheduled_at'
    ];

    public function template()
    {
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }
}
