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
            // Manajemen data dasar
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

            // Jabatan & administrasi pondok
            'manage jabatan',
            'assign jabatan',
            'view pondok dashboard',
            'manage keuangan',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ==============================
        // 📘 Role-permission Matrix
        // ==============================
        $rolePermissions = [
            'superadmin' => $permissions,

            'admin' => [
                'view santri', 'create santri', 'edit santri', 'delete santri',
                'view guru', 'create guru', 'edit guru',
                'view laporan', 'generate laporan',
                'view hafalan', 'view halaqoh',
                'manage jabatan', 'assign jabatan',
            ],

            'admin_unit' => [
                'view santri', 'create santri', 'edit santri', 'delete santri',
                'view guru', 'create guru', 'edit guru',
                'view laporan', 'generate laporan',
                'view hafalan', 'view halaqoh',
                'manage jabatan', 'assign jabatan',
            ],

            'kepala_madrasah' => [
                'view santri', 'edit santri',
                'view guru', 'edit guru',
                'view laporan', 'generate laporan',
                'assign jabatan',
                'view hafalan',
            ],

            'wakamad_kurikulum' => [
                'view santri', 'view guru',
                'view laporan',
                'view hafalan', 'manage halaqoh',
            ],

            'wakamad_kesiswaan' => [
                'view santri',
                'view laporan',
                'view hafalan',
            ],

            'wakamad_sarpras' => [
                'view unit', 'edit unit',
                'view laporan',
            ],

            'bendahara' => [
                'view laporan', 'generate laporan',
                'manage keuangan',
            ],

            'wali_kelas' => [
                'view santri',
                'view hafalan',
            ],

            'guru' => [
                'view hafalan', 'input hafalan', 'view halaqoh',
            ],

            'koor_tahfizh_putra' => [
                'view hafalan', 'input hafalan', 'view halaqoh', 'manage halaqoh',
            ],
            'koor_tahfizh_putri' => [
                'view hafalan', 'input hafalan', 'view halaqoh', 'manage halaqoh',
            ],

            'mudir_pondok' => [
                'view pondok dashboard',
                'view laporan', 'generate laporan',
                'manage jabatan', 'assign jabatan',
            ],

            'naibul_mudir' => [
                'view pondok dashboard',
                'view laporan',
                'assign jabatan',
            ],

            'naibatul_mudir' => [
                'view pondok dashboard',
                'view laporan',
                'assign jabatan',
            ],

            'kabag_kesantrian_putra' => [
                'view pondok dashboard',
                'view hafalan', 'manage halaqoh',
                'assign jabatan',
            ],
            'kabag_kesantrian_putri' => [
                'view pondok dashboard',
                'view hafalan', 'manage halaqoh',
                'assign jabatan',
            ],

            'kabag_umum' => [
                'view pondok dashboard',
                'view laporan',
                'assign jabatan',
            ],

            'koor_kesehatan_putra' => ['view pondok dashboard'],
            'koor_kesehatan_putri' => ['view pondok dashboard'],
            'koor_kebersihan_putra' => ['view pondok dashboard'],
            'koor_kebersihan_putri' => ['view pondok dashboard'],
            'koor_keamanan_putra' => ['view pondok dashboard'],
            'koor_keamanan_putri' => ['view pondok dashboard'],
            'koor_lughoh_putra' => ['view pondok dashboard'],
            'koor_lughoh_putri' => ['view pondok dashboard'],
            'koor_kepegawaian' => ['view pondok dashboard'],
            'koor_sarpras' => ['view pondok dashboard'],
            'koor_dapur' => ['view pondok dashboard'],
            'koor_logistik' => ['view pondok dashboard'],

            'pimpinan' => [
                'view laporan', 'generate laporan',
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
