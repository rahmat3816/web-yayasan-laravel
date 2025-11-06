<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

// Fortify custom login response binding
use App\Http\Responses\LoginResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

// Observer & Model
use App\Models\Guru;
use App\Observers\GuruObserver;

// Quran services
use App\Services\QuranMapService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind custom Fortify LoginResponse
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        // Quran map dari DB sebagai singleton (digunakan SetoranHafalanController)
        $this->app->singleton(QuranMapService::class, function ($app) {
            return new QuranMapService(); // membaca tabel quran_juz_map
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fortify: gunakan view login kustom
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // Observer: saat Guru dibuat, otomatis buat akun user (username, linked_guru_id, dst)
        Guru::observe(GuruObserver::class);
    }
}
