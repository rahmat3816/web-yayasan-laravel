<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Santri;
use App\Models\User;
use App\Models\WaliSantri;
use Illuminate\Support\Facades\Hash;

class WaliSantriSeeder extends Seeder
{
    public function run(): void
    {
        $santriList = Santri::all();

        foreach ($santriList as $santri) {
            $username = $santri->generateWaliUsername();

            $user = User::firstOrCreate(
                ['email' => $username . '@siyasgo.id'],
                [
                    'name' => $santri->nama ?? ('Wali Santri #' . $santri->id),
                    'username' => $username,
                    'password' => Hash::make('password'),
                ]
            );

            $user->assignRole('wali_santri');

            WaliSantri::firstOrCreate([
                'santri_id' => $santri->id,
                'user_id' => $user->id,
                'username_wali' => $username,
            ]);
        }
    }
}
