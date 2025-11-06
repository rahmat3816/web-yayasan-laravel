<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // 🧩 Tambahkan binding dummy agar Fortify tidak error di testing
        $this->app->bind(CreatesNewUsers::class, function () {
            return new class implements CreatesNewUsers {
                public function create(array $input)
                {
                    // Tidak dipakai karena login manual
                    return null;
                }
            };
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Dibiarkan kosong — login manual tidak pakai Fortify
    }
}
