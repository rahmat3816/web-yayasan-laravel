<?php
// ==============================
// 🧩 Pemeriksaan Kode LoginResponse.php
// File: app/Http/Responses/LoginResponse.php
// ==============================

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();

        // 🟡 Validasi awal jika belum login
        if (!$user) {
            return redirect('/login');
        }

        // 🟢 Ambil role pertama dari user dan ubah ke huruf kecil
        $role = strtolower($user->getRoleNames()->first() ?? '');

        // 🧭 Redirect otomatis berdasarkan role
        switch ($role) {
            case 'superadmin':
                return redirect()->intended('/admin/dashboard');

            case 'admin':
            case 'operator':
                return redirect()->intended('/admin/dashboard');

            case 'guru':
            case 'wali_kelas':
                return redirect()->intended('/guru/dashboard');

            case 'pimpinan':
                return redirect()->intended('/pimpinan/dashboard');

            case 'koordinator_tahfizh_putra':
            case 'koordinator_tahfizh_putri':
                return redirect()->intended('/tahfizh/dashboard');

            case 'wali_santri':
                return redirect()->intended('/wali/dashboard');

            default:
                return redirect()->intended('/dashboard');
        }
    }
}

// ==============================
// ✅ Hasil Pemeriksaan:
// - Kode sudah benar dan aman.
// - Fungsi redirect sesuai struktur route yang kita buat di Tahap 10.2.
// - Case-sensitive diatasi dengan strtolower().
// - Jika user tanpa role (kosong), akan diarahkan ke /dashboard default.
// ==============================

// ⚠️ Tips Debug jika redirect tetap 404:
// 1️⃣ Pastikan role di tabel model_spatie (roles & model_has_roles) sesuai nama pada switch-case.
// 2️⃣ Jalankan php artisan optimize:clear agar autoload tidak cache versi lama.
// 3️⃣ Pastikan URL tujuan (/guru/dashboard, /tahfizh/dashboard, dst) sudah aktif di route:list.
// ==============================