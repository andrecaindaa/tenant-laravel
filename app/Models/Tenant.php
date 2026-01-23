<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];


    public function currentPlan()
{
    return $this->subscription?->plan ?? Plan::where('slug', 'free')->first();
}

public function onTrial(): bool
{
    return $this->subscription?->trial_ends_at && $this->subscription->trial_ends_at->isFuture();
}

public function trialDaysLeft(): int
{
    if (!$this->subscription?->trial_ends_at) return 0;

    $days = now()->diffInDays($this->subscription->trial_ends_at, false);
    return max(0, $days);
}

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    // CORREÇÃO: Este método deve retornar o plano através da subscription
    public function getPlanAttribute()
    {
        return $this->subscription?->plan;
    }

    // Mantenha este método para relação
    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->where('status', 'active')
            ->latest();
    }
/*
    // Adicione este método para compatibilidade
    public function currentPlan()
    {
        return $this->plan;
    }*/

    public function limit(string $key): ?int
    {
        return $this->plan?->limits[$key] ?? null;
    }

    public function hasFeature(string $feature): bool
    {
        return $this->plan?->features[$feature] ?? false;
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function usage(): array
    {
        return [
            'projects' => $this->projects()->count(),
            'users'    => $this->users()->count(),
        ];
    }
/*
    // Adicione estes métodos importantes
    public function onTrial(): bool
    {
        return $this->subscription?->onTrial() ?? false;
    }

    public function trialDaysLeft(): ?int
    {
        return $this->subscription?->trialDaysLeft();
    }*/

    public function hasReachedLimit($metric): bool
    {
        $limit = $this->limit($metric);
        if ($limit === null) return false;

        $usage = $this->usage()[$metric] ?? 0;
        return $usage >= $limit;
    }


public function subscriptions()
{
    return $this->hasMany(Subscription::class);
}

public function activeSubscription()
{
    return $this->subscriptions()
        ->where('status', 'active')
        ->latest()
        ->first();
}
}
