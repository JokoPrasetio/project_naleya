<?php

return [
    'permissions' => [
        'dashboard.view' => 'Lihat dashboard',
        'registrations.view' => 'Lihat pendaftaran',
        'registrations.manage' => 'Kelola pendaftaran',
        'master.examinations.view' => 'Lihat master pemeriksaan',
        'master.examinations.manage' => 'Kelola master pemeriksaan',
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
            'label' => 'Pendaftaran',
            'items' => [
                ['label' => 'Daftar Pendaftaran', 'icon' => 'bi-clipboard2-pulse', 'route' => 'registrations.index', 'permission' => 'registrations.view'],
            ],
        ],
        [
            'label' => 'Master',
            'items' => [
                ['label' => 'Pemeriksaan', 'icon' => 'bi-diagram-3', 'route' => 'master.examinations.index', 'permission' => 'master.examinations.view'],
                ['label' => 'Jenis-Pemeriksaan', 'icon' => 'bi-diagram-3', 'route' => 'master.examinations.index', 'permission' => 'master.type_examinations.view'],
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
