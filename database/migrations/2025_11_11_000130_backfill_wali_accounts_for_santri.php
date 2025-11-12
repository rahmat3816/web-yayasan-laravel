<?php

use App\Models\Santri;
use App\Models\User;
use App\Models\WaliSantri;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        Santri::doesntHave('wali')
            ->with('unit')
            ->chunk(100, function ($santriChunk) {
                foreach ($santriChunk as $santri) {
                    $username = $this->uniqueUsername($santri);
                    $email = $username . '@wali.siyasgo.id';

                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name' => $santri->nama ?: 'Wali Santri',
                            'username' => $username,
                            'password' => Hash::make('password'),
                            'role' => 'wali_santri',
                        ]
                    );

                    if (! $user->hasRole('wali_santri')) {
                        $user->assignRole('wali_santri');
                    }

                    WaliSantri::firstOrCreate(
                        ['santri_id' => $santri->id, 'user_id' => $user->id],
                        ['username_wali' => $username]
                    );
                }
            });
    }

    public function down(): void
    {
        // Tidak perlu rollback karena akun wali tetap dibutuhkan.
    }

    protected function uniqueUsername(Santri $santri): string
    {
        do {
            $username = $santri->generateWaliUsername();
        } while (
            DB::table('users')->where('username', $username)->exists()
            || DB::table('wali_santri')->where('username_wali', $username)->exists()
        );

        return $username;
    }
};
