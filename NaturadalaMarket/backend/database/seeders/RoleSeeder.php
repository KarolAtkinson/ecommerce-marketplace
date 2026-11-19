<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Customer', 'slug' => 'customer'],
            ['name' => 'Retailer', 'slug' => 'retailer'],
            ['name' => 'Administrator', 'slug' => 'admin'],
        ] as $role) {
            Role::query()->firstOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
