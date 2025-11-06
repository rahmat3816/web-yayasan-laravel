<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guru;
use App\Models\User;

class GuruEnsureAccounts extends Command
{
    protected $signature = 'guru:ensure-accounts
                            {--unit= : Batasi pada unit_id tertentu}
                            {--fix-username : Generate username jika kosong ATAU masih memakai prefix lama "guru."}
                            {--rebuild-all : Paksa semua username guru dibentuk ulang ke pola baru}
                            {--dry-run : Simulasi saja, tidak menulis DB}';

    protected $description = 'Pastikan setiap guru memiliki akun user. Pola username: {kata_pertama}{2digit}. Email di-set ke {username}@yayasan.local.';

    public function handle(): int
    {
        $unit  = $this->option('unit');
        $dry   = (bool) $this->option('dry-run');
        $fixU  = (bool) $this->option('fix-username');
        $reall = (bool) $this->option('rebuild-all');

        $q = Guru::query();
        if ($unit) $q->where('unit_id', (int) $unit);

        $count = $q->count();
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $created = 0; $updated = 0;

        foreach ($q->cursor() as $g) {
            $user = User::where('linked_guru_id', $g->id)->first();

            if (!$user) {
                $username = $this->generateUsername2Digits($g->nama);
                $payload = [
                    'name'           => $g->nama,
                    'email'          => $username.'@yayasan.local',
                    'username'       => $username,
                    'password'       => bcrypt('password'),
                    'role'           => 'guru',
                    'unit_id'        => $g->unit_id,
                    'linked_guru_id' => $g->id,
                ];
                if (!$dry) {
                    $user = User::create($payload);
                    if (method_exists($user,'assignRole') && !$user->hasRole('guru')) {
                        $user->assignRole('guru');
                    }
                }
                $created++;
            } else {
                $needSave = false;

                // Rebuild username jika diminta
                if ($reall || ($fixU && (empty($user->username) || str_starts_with($user->username,'guru.')))) {
                    $user->username = $this->generateUsername2Digits($g->nama);
                    $needSave = true;
                }

                // Email selaras dengan username bila kosong/format lama
                if ($reall || $fixU || empty($user->email) || str_starts_with((string)$user->email,'guru.')) {
                    $user->email = $user->username.'@yayasan.local';
                    $needSave = true;
                }

                if ($user->unit_id !== $g->unit_id) {
                    $user->unit_id = $g->unit_id;
                    $needSave = true;
                }

                if ($needSave && !$dry) {
                    $user->save();
                }

                if (!$dry && method_exists($user,'assignRole') && !$user->hasRole('guru')) {
                    $user->assignRole('guru');
                }

                $updated++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Done. Created: {$created}, Updated: {$updated}. Dry-run: ".($dry ? 'yes' : 'no'));

        return self::SUCCESS;
    }

    /**
     * Username = kata_pertama_nama (lowercase, alnum) + 2 digit unik (01..99), dipilih secara acak.
     * Email = {{username}}@yayasan.local (dibentuk di tempat lain memakai username ini).
     */
    private function generateUsername2Digits(string $nama): string
    {
        // Kata pertama saja, huruf/angka saja, lowercase
        $first = trim(preg_split('/\s+/', $nama)[0] ?? '');
        $base  = strtolower(preg_replace('/[^a-z0-9]/i', '', $first));
        if ($base === '') {
            $base = 'user';
        }

        // Ambil username yang sudah ada dengan prefix $base
        $existing = \App\Models\User::where('username', 'like', $base.'%')
            ->pluck('username')
            ->all();

        // Kumpulkan suffix 2 digit yang sudah terpakai
        $used = [];
        foreach ($existing as $u) {
            if (preg_match('/^'.preg_quote($base,'/').'(\d{2})$/', $u, $m)) {
                $used[(int)$m[1]] = true;
            }
        }

        // Buat pool kandidat 01..99 yang belum terpakai, lalu acak
        $candidates = [];
        for ($i = 1; $i <= 99; $i++) {
            if (!isset($used[$i])) {
                $candidates[] = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            }
        }
        shuffle($candidates);

        // Pilih acak dari kandidat yang tersedia
        foreach ($candidates as $suffix) {
            $username = $base.$suffix;
            // double-check unik (race condition guard)
            if (!\App\Models\User::where('username', $username)->exists()) {
                return $username;
            }
        }

        // Fallback terakhir (seharusnya tidak terjadi)
        return $base . str_pad((string)random_int(1, 99), 2, '0', STR_PAD_LEFT);
    }

}
