<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

use App\Models\Unit;
use App\Policies\UnitPolicy;
use App\Models\SantriHealthLog;
use App\Policies\SantriHealthLogPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Unit::class => UnitPolicy::class,
        SantriHealthLog::class => SantriHealthLogPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Gate praktis: 'manage-units' → hanya SUPERADMIN
        Gate::define('manage-units', function ($user) {
            return strtolower($user->role ?? '') === 'superadmin'
                || (method_exists($user, 'hasRole') && $user->hasRole('superadmin'));
        });
    }
}
