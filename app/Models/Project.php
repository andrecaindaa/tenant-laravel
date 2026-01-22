<?php

namespace App\Models;

use App\Models\Concerns\TenantAwareModel;

class Project extends TenantAwareModel
{
    protected $fillable = ['name'];

}
