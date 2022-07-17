<?php

return [
    'name' => 'UserManagement',
    'admin_prefix' => 'admin',

    // exception for middleware checking
    'permission_exceptions' => [
        'admin.profile',
        'admin.profile_update'
    ]
];
