<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $configuredRoles = array_keys(config('jabatan.roles', []));

        $roles = array_unique(array_merge(
            ['superadmin', 'guru', 'pimpinan', 'wali_santri'],
            $configuredRoles
        ));

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }
    }
}
