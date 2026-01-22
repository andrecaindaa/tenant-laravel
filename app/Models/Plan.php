<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'limits',
        'active',
    ];

    protected $casts = [
        'limits' => 'array',
        'active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }
}
