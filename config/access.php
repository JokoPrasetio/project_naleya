<?php

return [
    'permissions' => [
        'dashboard.view' => 'Lihat dashboard',
        'access.users.view' => 'Lihat pengguna',
        'access.users.manage' => 'Kelola pengguna',
        'access.roles.view' => 'Lihat peran dan izin',
        'access.roles.manage' => 'Kelola peran dan izin',
    ],

    'menus' => [
        [
            'label' => 'Dashboard',
            'items' => [
                ['label' => 'Dashboard', 'icon' => 'bi-house-fill', 'route' => 'dashboard', 'permission' => 'dashboard.view'],
            ],
        ],
        [
            'label' => 'Pengaturan',
            'items' => [
                ['label' => 'Pengguna', 'icon' => 'bi-people', 'route' => 'access.users.index', 'permission' => 'access.users.view'],
                ['label' => 'Peran & Izin', 'icon' => 'bi-shield-lock', 'route' => 'access.roles.index', 'permission' => 'access.roles.view'],
            ],
        ],
    ],
];
