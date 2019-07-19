<?php

return [
    'default' => [
        ROLE_ADMIN => [
            'label' => ROLE_ADMIN_LABEL,
            'permissions' => [
                PERMISSION_ADMIN,
                PERMISSION_BACKEND_MANAGER,
                PERMISSION_BOAT_MANAGER,
                PERMISSION_WORKER,
            ]
        ],
        ROLE_BACKEND_MANAGER => [
            'label' => ROLE_BACKEND_MANAGER_LABEL,
            'permissions' => [
                PERMISSION_BACKEND_MANAGER,
                PERMISSION_BOAT_MANAGER,
            ]
        ],
        ROLE_BOAT_MANAGER => [
            'label' => ROLE_BOAT_MANAGER_LABEL,
            'permissions' => [
                PERMISSION_BOAT_MANAGER,
                PERMISSION_WORKER,
            ]
        ],
        ROLE_WORKER => [
            'label' => ROLE_WORKER_LABEL,
            'permissions' => [
                PERMISSION_WORKER,
            ]
        ]
    ]
];
