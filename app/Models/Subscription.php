<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'trial_ends_at',
        'ends_at',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}
