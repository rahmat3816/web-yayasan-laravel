<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ===============================
// 🔐 AUTH CONTROLLER
// ===============================
use App\Http\Controllers\AuthController;

// ===============================
// 📊 DASHBOARD CONTROLLERS
// ===============================
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\GuruDashboardController;
use App\Http\Controllers\Dashboard\WaliDashboardController;
use App\Http\Controllers\WaliController;
use App\Http\Controllers\Dashboard\PimpinanDashboardController;
use App\Http\Controllers\Dashboard\TahfizhDashboardController;
use App\Http\Controllers\Modules\KesantrianTahfizhController;
use App\Http\Controllers\Modules\KesantrianModuleController;
use App\Http\Controllers\Program\TahfizhQuranProgramController;

// ===============================
// 📚 MASTER DATA CONTROLLERS
// ===============================
use App\Http\Controllers\Admin\SantriController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\HalaqohController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\GuruRoleController;
use App\Http\Controllers\Admin\LaporanHafalanController;

use Filament\Pages\Dashboard;

// Route untuk halaman utama (biar gak 404)
Route::get('/', function () {
    return redirect('/filament');
    // atau return view('welcome');
});

// ===============================
// 🧾 SETORAN HAFALAN (GURU)
// ===============================
use App\Http\Controllers\Guru\SetoranHafalanController;

// ===============================
// 🧑‍🏫 PENUNJUKAN PENGAMPU (TAHFIZH)
// ===============================
use App\Http\Controllers\Tahfizh\PengampuController;

// =====================================================
// 🔐 LOGIN & LOGOUT
// =====================================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// =====================================================
// 📋 AREA LOGIN (AUTH REQUIRED)
// =====================================================
Route::middleware('auth')->group(function () {

    // ===============================
    // 🏠 DASHBOARD REDIRECT BY ROLE
    // ===============================
    Route::get('/dashboard', function () {
        $user = Auth::user();
        $role = strtolower($user->role ?? '');
        $adminRoles = [
            'superadmin',
            'admin',
            'admin_unit',
            'kepala_madrasah',
            'wakamad_kurikulum',
            'wakamad_kesiswaan',
            'wakamad_sarpras',
            'bendahara',
        ];

        $pondokLeadership = [
            'pimpinan',
            'mudir_pondok',
            'naibul_mudir',
            'naibatul_mudir',
            'kabag_kesantrian_putra',
            'kabag_kesantrian_putri',
            'kabag_umum',
            'koor_kesehatan_putra',
            'koor_kesehatan_putri',
            'koor_kebersihan_putra',
            'koor_kebersihan_putri',
            'koor_keamanan_putra',
            'koor_keamanan_putri',
            'koor_tahfizh_putra',
            'koor_tahfizh_putri',
            'koor_lughoh_putra',
            'koor_lughoh_putri',
            'koor_kepegawaian',
            'koor_sarpras',
            'koor_dapur',
            'koor_logistik',
        ];

        if (in_array($role, $adminRoles, true)) {
            return redirect()->route('admin.dashboard');
        }

        if (in_array($role, ['guru', 'wali_kelas'], true)) {
            return redirect()->route('guru.dashboard');
        }

        if (in_array($role, ['koordinator_tahfizh_putra', 'koordinator_tahfizh_putri'], true)) {
            return redirect()->route('tahfizh.dashboard');
        }

        if (in_array($role, $pondokLeadership, true)) {
            return redirect()->route('pimpinan.dashboard');
        }

        return match ($role) {
            'wali_santri' => redirect()->route('wali.dashboard'),
            default => view('dashboard'),
        };
    })->name('dashboard');

    // =====================================================
    // 👨‍💼 ADMIN & OPERATOR
    // =====================================================
    Route::prefix('admin')
        ->middleware('role:superadmin|admin|admin_unit|kepala_madrasah|wakamad_kurikulum|wakamad_kesiswaan|wakamad_sarpras|bendahara|mudir_pondok|naibul_mudir|naibatul_mudir|kabag_kesantrian_putra|kabag_kesantrian_putri|kabag_umum')
        ->group(function () {

            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

            // 🧒 SANTRI
            Route::controller(SantriController::class)->group(function () {
                Route::get('/santri', 'index')->name('admin.santri.index');
                Route::get('/santri/create', 'create')->name('admin.santri.create');
                Route::post('/santri', 'store')->name('admin.santri.store');
                Route::get('/santri/{id}', 'show')->name('admin.santri.show');
                Route::get('/santri/{id}/edit', 'edit')->name('admin.santri.edit');
                Route::put('/santri/{id}', 'update')->name('admin.santri.update');
                Route::delete('/santri/{id}', 'destroy')->name('admin.santri.destroy');
            });

            // 👨‍🏫 GURU
            Route::controller(GuruController::class)->group(function () {
                Route::get('/guru', 'index')->name('admin.guru.index');
                Route::get('/guru/create', 'create')->name('admin.guru.create');
                Route::post('/guru', 'store')->name('admin.guru.store');
                Route::get('/guru/{id}', 'show')->name('admin.guru.show');
                Route::get('/guru/{id}/edit', 'edit')->name('admin.guru.edit');
                Route::put('/guru/{id}', 'update')->name('admin.guru.update');
                Route::delete('/guru/{id}', 'destroy')->name('admin.guru.destroy');
            });

            // 🪶 JABATAN GURU
            Route::controller(GuruRoleController::class)->group(function () {
                Route::get('/guru/jabatan', 'index')->name('admin.guru.jabatan.index');
                Route::get('/guru/{guruId}/jabatan/edit', 'edit')->name('admin.guru.jabatan.edit');
                Route::put('/guru/{guruId}/jabatan', 'update')->name('admin.guru.jabatan.update');
            });

            // 📖 HALAQOH
            Route::get('/halaqoh/santri-by-guru/{guruId}', [HalaqohController::class, 'getSantriByGuru'])
                ->name('admin.halaqoh.santriByGuru');

            Route::controller(HalaqohController::class)->group(function () {
                Route::get('/halaqoh', 'index')->name('admin.halaqoh.index');
                Route::get('/halaqoh/create', 'create')->name('admin.halaqoh.create');
                Route::post('/halaqoh', 'store')->name('admin.halaqoh.store');
                Route::get('/halaqoh/{id}', 'show')->name('admin.halaqoh.show');
                Route::get('/halaqoh/{id}/edit', 'edit')->name('admin.halaqoh.edit');
                Route::put('/halaqoh/{id}', 'update')->name('admin.halaqoh.update');
                Route::delete('/halaqoh/{id}', 'destroy')->name('admin.halaqoh.destroy');
            });

            // 📈 LAPORAN
            Route::get('/laporan', [LaporanController::class, 'index'])->name('admin.laporan.index');

            // 📈 LAPORAN HAFALAN QURAN
            Route::get('/laporan/hafalan', [LaporanHafalanController::class, 'index'])
                ->name('admin.laporan.hafalan');
            Route::get('laporan/hafalan/santri/{id}/grafik', [LaporanHafalanController::class, 'grafikSantri'])
                ->name('admin.laporan.hafalan.grafikSantri');
        });

    // =====================================================
    // 🏫 UNIT (SUPERADMIN ONLY)
    // =====================================================
    Route::prefix('admin')
        ->middleware('role:superadmin')
        ->group(function () {
            Route::controller(UnitController::class)->group(function () {
                Route::get('/unit', 'index')->name('admin.unit.index');
                Route::get('/unit/create', 'create')->name('admin.unit.create');
                Route::post('/unit', 'store')->name('admin.unit.store');
                Route::get('/unit/{id}', 'show')->name('admin.unit.show');
                Route::get('/unit/{id}/edit', 'edit')->name('admin.unit.edit');
                Route::put('/unit/{id}', 'update')->name('admin.unit.update');
                Route::delete('/unit/{id}', 'destroy')->name('admin.unit.destroy');
            });
        });

    // =====================================================
    // 👨‍🏫 GURU + KOORDINATOR + SUPERADMIN
    // =====================================================
    Route::prefix('guru')
    ->middleware('role:guru|koordinator_tahfizh_putra|koordinator_tahfizh_putri|superadmin')
    ->group(function () {

        Route::get('/dashboard', [GuruDashboardController::class, 'index'])->name('guru.dashboard');

        // 📋 Daftar setoran & rekap
        Route::middleware('ensure.setoran.list.access')->group(function () {
            Route::get('/setoran', [SetoranHafalanController::class, 'index'])->name('guru.setoran.index');
            Route::get('/setoran/rekap', [SetoranHafalanController::class, 'rekap'])->name('guru.setoran.rekap');
        });

        // ✍️ Input setoran (guru pengampu)
        Route::middleware('ensure.guru.pengampu')->group(function () {
            Route::get('/setoran/santri/{santriId}/create', [SetoranHafalanController::class, 'create'])->name('guru.setoran.create');
            Route::post('/setoran/santri/{santriId}', [SetoranHafalanController::class, 'store'])->name('guru.setoran.store');
        });

        // === AJAX endpoint (untuk form interaktif)
        Route::get('/setoran/ajax/get-setoran-santri/{santriId}', [SetoranHafalanController::class, 'getSetoranSantri'])
            ->name('guru.setoran.ajax.getSetoranSantri');

        Route::get('/setoran/ajax/get-surat-by-juz/{juz}', [SetoranHafalanController::class, 'getSuratByJuz'])
            ->name('guru.setoran.ajax.getSuratByJuz');

        Route::view('/laporan', 'guru.laporan.index')->name('guru.laporan.index');
    });


    // =====================================================
    // 👨‍👩‍👧‍👦 WALI SANTRI
    // =====================================================
    Route::prefix('wali')
        ->middleware('role:wali_santri|superadmin')
        ->group(function () {
            Route::get('/dashboard', [WaliDashboardController::class, 'index'])->name('wali.dashboard');
            Route::get('/profil', [WaliController::class, 'profil'])->name('wali.profil');
            Route::get('/progres', [WaliController::class, 'progres'])->name('wali.progres');
            Route::get('/hafalan', [WaliController::class, 'hafalan'])->name('wali.hafalan');
        });

    // =====================================================
    // 🧕 PIMPINAN
    // =====================================================
    Route::prefix('pimpinan')
        ->middleware('role:pimpinan|mudir_pondok|naibul_mudir|naibatul_mudir|kabag_kesantrian_putra|kabag_kesantrian_putri|kabag_umum|koor_kesehatan_putra|koor_kesehatan_putri|koor_kebersihan_putra|koor_kebersihan_putri|koor_keamanan_putra|koor_keamanan_putri|koor_tahfizh_putra|koor_tahfizh_putri|koor_lughoh_putra|koor_lughoh_putri|koor_kepegawaian|koor_sarpras|koor_dapur|koor_logistik|superadmin')
        ->group(function () {
            Route::get('/dashboard', [PimpinanDashboardController::class, 'index'])->name('pimpinan.dashboard');
        });

    // =====================================================
    // 📖 KOORDINATOR TAHFIZH
    // =====================================================
    Route::prefix('tahfizh')
        ->middleware([
            'role:koordinator_tahfizh_putra|koordinator_tahfizh_putri|admin|admin_unit|superadmin',
        ])
        ->group(function () {
            Route::get('/dashboard', function () {
                return redirect()->to('/filament/tahfizh-dashboard');
            })->name('tahfizh.dashboard');
            Route::get('/dashboard/timeline', [TahfizhDashboardController::class, 'timeline'])->name('tahfizh.dashboard.timeline');
            Route::post('/target', [TahfizhDashboardController::class, 'storeTarget'])->name('tahfizh.target.store');
            Route::get('/ajax/surat-by-juz/{juz}', [SetoranHafalanController::class, 'getSuratByJuz'])->name('tahfizh.ajax.suratByJuz');
            Route::get('/target/preview', [TahfizhDashboardController::class, 'previewTarget'])->name('tahfizh.target.preview');
            Route::get('/coverage/{santri}', [TahfizhDashboardController::class, 'coverageDetail'])->name('tahfizh.coverage.detail');

            Route::controller(PengampuController::class)->group(function () {
                Route::get('/halaqoh', 'index')->name('tahfizh.halaqoh.index');
                // 🔹 AJAX - Daftar guru & santri otomatis
                Route::get('/halaqoh/santri-by-guru/{guruId}', 'getSantriByGuru')->name('tahfizh.halaqoh.santriByGuru');
                Route::get('/ajax/guru-by-unit/{unitId}', 'getGuruByUnit')->name('tahfizh.ajax.guruByUnit');
                Route::get('/ajax/santri-by-unit/{unitId}', 'getSantriByUnit')->name('tahfizh.ajax.santriByUnit');

                Route::get('/halaqoh/create', 'create')->name('tahfizh.halaqoh.create');
                Route::post('/halaqoh', 'store')->name('tahfizh.halaqoh.store');
                Route::get('/halaqoh/{id}/pengampu/edit', 'edit')->name('tahfizh.halaqoh.pengampu.edit');
                Route::put('/halaqoh/{id}/pengampu', 'update')->name('tahfizh.halaqoh.pengampu.update');

            });
        });

});

// =====================================================
// FILAMENT v3.3 ADMIN PANEL (WAJIB ADA!)
// =====================================================
require __DIR__.'/../vendor/filament/filament/routes/web.php';

Route::middleware('auth')->group(function () {
    Route::view('/modul/kepala-madrasah', 'modules.kepala-madrasah')
        ->middleware('role:kepala_madrasah|superadmin')
        ->name('module.kepala');

    Route::view('/modul/wakamad-kurikulum', 'modules.wakamad-kurikulum')
        ->middleware('role:wakamad_kurikulum|superadmin')
        ->name('module.wakamad.kurikulum');

    Route::view('/modul/wakamad-kesiswaan', 'modules.wakamad-kesiswaan')
        ->middleware('role:wakamad_kesiswaan|superadmin')
        ->name('module.wakamad.kesiswaan');

    Route::view('/modul/wakamad-sarpras', 'modules.wakamad-sarpras')
        ->middleware('role:wakamad_sarpras|superadmin')
        ->name('module.wakamad.sarpras');

    Route::view('/modul/bendahara', 'modules.bendahara')
        ->middleware('role:bendahara|superadmin')
        ->name('module.bendahara');

    Route::view('/modul/wali-kelas', 'modules.wali-kelas')
        ->middleware('role:wali_kelas|superadmin')
        ->name('module.wali-kelas');

    Route::view('/modul/guru-mapel', 'modules.guru-mapel')
        ->middleware('role:guru_mapel_umum|guru_mapel_syari|superadmin')
        ->name('module.guru-mapel');

    Route::view('/modul/mudir-pondok', 'modules.mudir')
        ->middleware('role:mudir_pondok|naibul_mudir|naibatul_mudir|superadmin')
        ->name('module.mudir');

    Route::get('/modul/kesantrian-putra', [KesantrianModuleController::class, 'putra'])
        ->middleware('role:kabag_kesantrian_putra|superadmin')
        ->name('module.kesantrian.putra');

    Route::get('/modul/kesantrian-putri', [KesantrianModuleController::class, 'putri'])
        ->middleware('role:kabag_kesantrian_putri|superadmin')
        ->name('module.kesantrian.putri');

    Route::view('/modul/kabag-umum', 'modules.kabag-umum')
        ->middleware('role:kabag_umum|superadmin')
        ->name('module.kabag-umum');

    Route::view('/modul/koor-kesehatan', 'modules.koor-kesehatan')
        ->middleware('role:koor_kesehatan_putra|koor_kesehatan_putri|superadmin')
        ->name('module.koor-kesehatan');

    Route::view('/modul/koor-kebersihan', 'modules.koor-kebersihan')
        ->middleware('role:koor_kebersihan_putra|koor_kebersihan_putri|superadmin')
        ->name('module.koor-kebersihan');

    Route::view('/modul/koor-keamanan', 'modules.koor-keamanan')
        ->middleware('role:koor_keamanan_putra|koor_keamanan_putri|superadmin')
        ->name('module.koor-keamanan');

    Route::get('/modul/kesantrian/{segment}/tahfizh', [KesantrianTahfizhController::class, 'show'])
        ->whereIn('segment', ['putra', 'putri'])
        ->middleware('role:kabag_kesantrian_putra|kabag_kesantrian_putri|koor_tahfizh_putra|koor_tahfizh_putri|superadmin')
        ->name('module.kesantrian.tahfizh');

    Route::get('/program/tahfizh-quran', [TahfizhQuranProgramController::class, 'index'])
        ->middleware('role:superadmin|admin|admin_unit|koordinator_tahfizh_putra|koordinator_tahfizh_putri|guru|wali_kelas')
        ->name('program.tahfizh-quran');
});

Route::fallback(function () {
    if (auth()->check()) {
        return response()
            ->view('errors.under-development', [], 200);
    }

    return redirect()->route('dashboard');
});
