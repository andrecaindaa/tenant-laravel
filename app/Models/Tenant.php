<?php



namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Plan;

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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function plan(): ?Plan
    {
        return $this->subscription?->plan;
    }

    public function limit(string $key): ?int
    {
        return $this->plan()?->limits[$key] ?? null;
    }

}
