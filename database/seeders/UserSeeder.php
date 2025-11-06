<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ✅ Buat dummy unit jika belum ada (hindari FK error)
        if (Schema::hasTable('units') && DB::table('units')->count() === 0) {
            DB::table('units')->insert([
                ['id' => 1, 'nama_unit' => 'TK Permata Sunnah'],
                ['id' => 2, 'nama_unit' => 'MI Imam Syafi\'i'],
                ['id' => 3, 'nama_unit' => 'MTS Imam Syafi\'i'],
                ['id' => 4, 'nama_unit' => 'MTS As-Sunnah Gorontalo'],
                ['id' => 5, 'nama_unit' => 'MA As-Sunnah Limboto Barat'],
                ['id' => 6, 'nama_unit' => 'Ponpes As-Sunnah Gorontalo'],
                ['id' => 7, 'nama_unit' => 'Ponpes UMA Gorontalo'],
            ]);
        }

        // 🎯 Daftar akun default + username yang diinginkan
        $users = [
            // 🧑‍💼 SUPERADMIN
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@yayasan.test',
                'username' => 'superadmin',
                'password' => 'password',
                'role' => 'superadmin',
                'unit_id' => null,
            ],

            // 🏫 ADMIN per UNIT
            [
                'name' => 'Admin TK Permata Sunnah',
                'email' => 'admin.tk@yayasan.test',
                'username' => 'admintk',
                'password' => 'password',
                'role' => 'admin',
                'unit_id' => 1
            ],
            [
                'name' => 'Admin MI Imam Syafi\'i',
                'email' => 'admin.mi@yayasan.test',
                'username' => 'adminmi',
                'password' => 'password',
                'role' => 'admin',
                'unit_id' => 2
            ],
            [
                'name' => 'Admin MTS Imam Syafi\'i',
                'email' => 'admin.mts1@yayasan.test',
                'username' => 'adminmts1',
                'password' => 'password',
                'role' => 'admin',
                'unit_id' => 3
            ],
            [
                'name' => 'Admin MTS As-Sunnah Gorontalo',
                'email' => 'admin.mts2@yayasan.test',
                'username' => 'adminmts2',
                'password' => 'password',
                'role' => 'admin',
                'unit_id' => 4
            ],
            [
                'name' => 'Admin MA As-Sunnah Limboto Barat',
                'email' => 'admin.ma@yayasan.test',
                'username' => 'adminma',
                'password' => 'password',
                'role' => 'admin',
                'unit_id' => 5
            ],
            [
                'name' => 'Admin Ponpes As-Sunnah',
                'email' => 'admin.ponpes@yayasan.test',
                'username' => 'adminponpes',
                'password' => 'password',
                'role' => 'admin',
                'unit_id' => 6
            ],
            [
                'name' => 'Admin UMA Gorontalo',
                'email' => 'admin.uma@yayasan.test',
                'username' => 'adminuma',
                'password' => 'password',
                'role' => 'admin',
                'unit_id' => 7
            ],

            // 👨‍🏫 GURU & KOORDINATOR
            [
                'name' => 'Guru Al-Qur\'an',
                'email' => 'guru@yayasan.test',
                'username' => 'guruquran',
                'password' => 'password',
                'role' => 'guru',
                'unit_id' => 2
            ],
            [
                'name' => 'Koordinator Tahfizh Putra',
                'email' => 'koor.putra@yayasan.test',
                'username' => 'koorputra',
                'password' => 'password',
                'role' => 'koordinator_tahfizh_putra',
                'unit_id' => 2
            ],
            [
                'name' => 'Koordinator Tahfizh Putri',
                'email' => 'koor.putri@yayasan.test',
                'username' => 'koorputri',
                'password' => 'password',
                'role' => 'koordinator_tahfizh_putri',
                'unit_id' => 2
            ],

            // 🧕 MUDIR POPES
            [
                'name' => 'Mudir Popes As-Sunnah Gorontalo',
                'email' => 'mudir.popes@yayasan.test',
                'username' => 'mudirpopes',
                'password' => 'password',
                'role' => 'pimpinan',
                'unit_id' => 6
            ],

            // 🧕 KEPALA MADRASAH per UNIT
            [
                'name' => 'Kepala Sekolah TK Permata Sunnah',
                'email' => 'kepala.tk@yayasan.test',
                'username' => 'kepalatk',
                'password' => 'password',
                'role' => 'pimpinan',
                'unit_id' => 1
            ],

            [
                'name' => 'Kepala Madrasah MI Imam Syafi\'i',
                'email' => 'kepala.mi@yayasan.test',
                'username' => 'kepalami',
                'password' => 'password',
                'role' => 'pimpinan',
                'unit_id' => 2
            ],

            [
                'name' => 'Kepala Madrasah MTS Imam Syafi\'i',
                'email' => 'kepala.mts1@yayasan.test',
                'username' => 'kepalamts1',
                'password' => 'password',
                'role' => 'pimpinan',
                'unit_id' => 3
            ],

            [
                'name' => 'Kepala Madrasah MTS As-Sunnah Gorontalo',
                'email' => 'kepala.mts2@yayasan.test',
                'username' => 'kepalamts2',
                'password' => 'password',
                'role' => 'pimpinan',
                'unit_id' => 4
            ],

            [
                'name' => 'Kepala Madrasah MA As-Sunnah Limboto Barat',
                'email' => 'kepala.ma@yayasan.test',
                'username' => 'kepalama',
                'password' => 'password',
                'role' => 'pimpinan',
                'unit_id' => 5
            ],

            // 👨‍👩‍👧‍👦 WALI SANTRI
            [
                'name' => 'Wali Santri',
                'email' => 'wali@yayasan.test',
                'username' => 'wali',
                'password' => 'password',
                'role' => 'wali_santri',
                'unit_id' => null
            ],
        ];

        foreach ($users as $u) {
            // Siapkan username unik (kalau sudah dipakai akan ditambah angka)
            $desiredUsername = $u['username'];
            $username = $this->uniqueUsername($desiredUsername);

            // Hash password selalu saat seeding (aman untuk re-run)
            $hashedPassword = Hash::make($u['password']);

            // Upsert berdasarkan email (idempotent)
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name'   => $u['name'],
                    'username' => $username,
                    'password' => $hashedPassword,
                    'role'     => $u['role'],
                    'unit_id'  => $u['unit_id'],
                    'linked_guru_id'   => $user->linked_guru_id ?? null,   // biarkan tetap/null (tidak diubah di seeder)
                    'linked_santri_id' => $user->linked_santri_id ?? null, // biarkan tetap/null
                ]
            );

            // Jika ternyata user sudah ada dan belum punya username, set sekarang (dengan unik)
            if (empty($user->username)) {
                $user->username = $this->uniqueUsername($desiredUsername);
                $user->save();
            }

            // ✅ Assign role via Spatie jika tersedia
            if (
                method_exists($user, 'assignRole') &&
                class_exists(\Spatie\Permission\Models\Role::class) &&
                \Spatie\Permission\Models\Role::where('name', $u['role'])->exists()
            ) {
                if (!$user->hasRole($u['role'])) {
                    $user->assignRole($u['role']);
                }
            }
        }
    }

    /**
     * Hasilkan username unik. Jika sudah ada, append angka (2,3,...) sampai unik.
     */
    protected function uniqueUsername(string $base): string
    {
        $candidate = Str::lower(preg_replace('/[^a-z0-9_]/i', '', $base)) ?: 'user';
        if (!User::where('username', $candidate)->exists()) {
            return $candidate;
        }
        $i = 2;
        while (User::where('username', $candidate.$i)->exists()) {
            $i++;
            if ($i > 9999) {
                return $candidate.Str::random(4);
            }
        }
        return $candidate.$i;
    }
}
