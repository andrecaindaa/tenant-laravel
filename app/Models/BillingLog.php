<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'from_plan',
        'to_plan',
        'type',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
