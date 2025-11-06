<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // ==============================
        // 📘 Daftar Permission Global
        // ==============================
        $permissions = [
            // Manajemen data
            'view santri',
            'create santri',
            'edit santri',
            'delete santri',

            'view guru',
            'create guru',
            'edit guru',
            'delete guru',

            'view unit',
            'create unit',
            'edit unit',
            'delete unit',

            'view laporan',
            'generate laporan',

            // Hafalan & halaqoh
            'view hafalan',
            'input hafalan',
            'view halaqoh',
            'manage halaqoh',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ==============================
        // 📘 Role-permission Matrix
        // ==============================
        $rolePermissions = [
            'superadmin' => $permissions, // semua akses

            'admin' => [
                'view santri', 'create santri', 'edit santri', 'delete santri',
                'view guru', 'create guru', 'edit guru',
                'view unit', 'create unit', 'edit unit',
                'view laporan', 'generate laporan',
            ],

            'operator' => [
                'view santri', 'create santri', 'edit santri',
                'view guru', 'view unit', 'view laporan',
            ],

            'guru' => [
                'view hafalan', 'input hafalan', 'view halaqoh',
            ],

            'koordinator_tahfizh_putra' => [
                'view hafalan', 'input hafalan', 'view halaqoh', 'manage halaqoh',
            ],

            'koordinator_tahfizh_putri' => [
                'view hafalan', 'input hafalan', 'view halaqoh', 'manage halaqoh',
            ],

            'pimpinan' => [
                'view laporan', 'generate laporan',
            ],

            'wali_kelas' => [
                'view santri', 'view hafalan',
            ],

            'wali_santri' => [
                'view hafalan',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
