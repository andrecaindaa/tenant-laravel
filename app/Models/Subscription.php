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
        'current_period_ends_at',
        'canceled_at',
        'next_plan_id',
        'next_plan_starts_at',
        'usage',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'current_period_ends_at' => 'datetime',
        'canceled_at' => 'datetime',
        'next_plan_starts_at' => 'datetime',
        'usage' => 'array',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function nextPlan()
    {
        return $this->belongsTo(Plan::class, 'next_plan_id');
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }

    public function trialDaysLeft(): int
    {
        if (!$this->trial_ends_at) return 0;
        return max(0, now()->diffInDays($this->trial_ends_at));
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function hasScheduledDowngrade(): bool
    {
        return !is_null($this->next_plan_id) && !is_null($this->next_plan_starts_at);
    }

    public function applyScheduledDowngrade()
    {
        if (!$this->hasScheduledDowngrade() || !$this->next_plan_starts_at->isPast()) {
            return false;
        }

        $oldPlanId = $this->plan_id;
        $this->update([
            'plan_id' => $this->next_plan_id,
            'next_plan_id' => null,
            'next_plan_starts_at' => null,
        ]);

        // Log da alteração
        BillingLog::create([
            'tenant_id' => $this->tenant_id,
            'action' => 'downgrade_applied',
            'metadata' => [
                'from_plan_id' => $oldPlanId,
                'to_plan_id' => $this->next_plan_id,
                'automated' => true,
            ],
        ]);

        return true;
    }

    public function scopeActive($query)
{
    return $query->where('status', 'active');
}
}
