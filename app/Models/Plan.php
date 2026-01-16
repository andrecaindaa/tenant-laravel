<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'interval',
        'limits',
        'active',
    ];

    protected $casts = [
        'limits' => 'array',
    ];
}
