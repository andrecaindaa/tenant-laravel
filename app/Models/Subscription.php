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
        'next_plan_id',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at'       => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

     public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }

    public function active(): bool
    {
        return $this->status === 'active' && !$this->ended();
    }

    public function ended(): bool
    {
        return $this->ends_at && now()->gte($this->ends_at);
    }

    public function nextPlan()
    {
        return $this->belongsTo(Plan::class, 'next_plan_id');
    }

    public function hasScheduledDowngrade(): bool
    {
        return !is_null($this->next_plan_id);
    }


}
