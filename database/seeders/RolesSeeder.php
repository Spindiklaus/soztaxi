<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run()
    {
        Role::updateOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
    }
}