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
use App\Http\Controllers\Dashboard\PimpinanDashboardController;
use App\Http\Controllers\Dashboard\TahfizhDashboardController;

// ===============================
// 📚 MASTER DATA CONTROLLERS
// ===============================
use App\Http\Controllers\Admin\SantriController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\HalaqohController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\GuruRoleController;

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
        return match ($role) {
            'superadmin', 'admin', 'operator' => redirect()->route('admin.dashboard'),
            'guru', 'koordinator_tahfizh_putra', 'koordinator_tahfizh_putri' => redirect()->route('guru.dashboard'),
            'wali_santri' => redirect()->route('wali.dashboard'),
            'pimpinan' => redirect()->route('pimpinan.dashboard'),
            default => view('dashboard'),
        };
    })->name('dashboard');

    // =====================================================
    // 👨‍💼 ADMIN & OPERATOR
    // =====================================================
    Route::prefix('admin')
        ->middleware('role:superadmin|admin|operator')
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
        ->middleware('role:wali_santri')
        ->group(function () {
            Route::get('/dashboard', [WaliDashboardController::class, 'index'])->name('wali.dashboard');
            Route::view('/profil', 'wali.profil')->name('wali.profil');
            Route::view('/hafalan', 'wali.hafalan')->name('wali.hafalan');
        });

    // =====================================================
    // 🧕 PIMPINAN
    // =====================================================
    Route::prefix('pimpinan')
        ->middleware('role:pimpinan')
        ->group(function () {
            Route::get('/dashboard', [PimpinanDashboardController::class, 'index'])->name('pimpinan.dashboard');
        });

    // =====================================================
    // 📖 KOORDINATOR TAHFIZH
    // =====================================================
    Route::prefix('tahfizh')
        ->middleware('role:koordinator_tahfizh_putra|koordinator_tahfizh_putri|admin|superadmin')
        ->group(function () {
            Route::get('/dashboard', [TahfizhDashboardController::class, 'index'])->name('tahfizh.dashboard');

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
