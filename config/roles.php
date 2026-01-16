<?php

return [

    'owner' => [
        'tenant.manage',
        'users.manage',
        'billing.manage',
        'projects.create',
        'projects.update',
        'projects.delete',
        'projects.view',
    ],

    'admin' => [
        'users.manage',
        'projects.create',
        'projects.update',
        'projects.view',
    ],

    'member' => [
        'projects.view',
    ],

];
