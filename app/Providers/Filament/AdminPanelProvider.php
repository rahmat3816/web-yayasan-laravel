<?php

namespace App\Providers\Filament;

use App\Filament\Pages\SetoranHafalanCreate;
use App\Filament\Pages\SetoranHafalanRekap;
use App\Filament\Pages\SetoranHaditsCreate;
use App\Http\Controllers\Dashboard\TahfizhDashboardController;
use App\Http\Controllers\Guru\SetoranHafalanController;
use App\Http\Controllers\Tahfizh\HaditsSetoranFormController;
use App\Http\Controllers\Tahfizh\HaditsTargetPlannerController;
use App\Http\Controllers\Tahfizh\MutunTargetPlannerController;
use App\Http\Controllers\Tahfizh\MutunSetoranFormController;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Route;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('filament')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\MutunDashboard::class,
                \App\Filament\Pages\MutunTargets::class,
                \App\Filament\Pages\SetoranMutun::class,
                \App\Filament\Pages\RekapMutun::class,
            ])
            ->routes(function () {
                Route::prefix('tahfizh-dashboard')->name('pages.tahfizh-dashboard.')->group(function () {
                    Route::get('timeline', [TahfizhDashboardController::class, 'timeline'])
                        ->name('timeline');
                    Route::get('target-preview', [TahfizhDashboardController::class, 'previewTarget'])
                        ->name('preview-target');
                    Route::get('coverage/{santri}', [TahfizhDashboardController::class, 'coverageDetail'])
                        ->name('coverage-detail');
                    Route::get('surat-by-juz/{juz}', [SetoranHafalanController::class, 'getSuratByJuz'])
                        ->name('surat-by-juz');
                });
                Route::prefix('tahfizh/setoran-hafalan')->name('pages.setoran-hafalan.')->group(function () {
                    Route::get('{santri}/create', SetoranHafalanCreate::class)
                        ->name('create');
                    Route::post('{santri}', [SetoranHafalanController::class, 'store'])
                        ->name('store');
                    Route::get('ajax/setoran-santri/{santri}', [SetoranHafalanController::class, 'getSetoranSantri'])
                        ->name('ajax-santri');
                    Route::get('rekap', SetoranHafalanRekap::class)
                        ->name('rekap');
                });
                Route::post('hadits-targets', [HaditsTargetPlannerController::class, 'store'])
                    ->name('pages.hadits-targets.store');
                Route::post('mutun-targets', [MutunTargetPlannerController::class, 'store'])
                    ->name('pages.mutun-targets.store');
                Route::prefix('hadits-setorans')->name('pages.hadits-setorans.')->group(function () {
                    Route::get('create', \App\Filament\Pages\SetoranHaditsCreate::class)
                        ->name('create');
                    Route::post('/', [HaditsSetoranFormController::class, 'store'])
                        ->name('store');
                });
                Route::prefix('mutun-setorans')->name('pages.mutun-setorans.')->group(function () {
                    Route::get('create', \App\Filament\Pages\SetoranMutunCreate::class)
                        ->name('create');
                    Route::post('/', [MutunSetoranFormController::class, 'store'])
                        ->name('store');
                });
            })
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                'Dashboard',
                "Tahfizh Qur'an",
                'Tahfizh Hadits',
                'Tahfizh Mutun',
                'Kesantrian',
                'Bahasa',
                'Kesehatan',
                'Keamanan',
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Kembali ke Dashboard Yayasan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->url(url('/admin/dashboard'), shouldOpenInNewTab: false),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
