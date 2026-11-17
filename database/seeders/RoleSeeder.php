<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(
            ['slug' => 'administrator'],
            ['name' => 'Administrator', 'permissions' => [], 'is_system' => true],
        );

        Role::firstOrCreate(
            ['slug' => 'operator'],
            ['name' => 'Operator', 'permissions' => ['dashboard.view'], 'is_system' => false],
        );
    }
}
