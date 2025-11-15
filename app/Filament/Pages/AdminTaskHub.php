<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminTaskHub extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static ?string $navigationLabel = 'Task Hub';

    protected static ?string $navigationGroup = 'Dashboard';

    protected static ?int $navigationSort = -2;

    protected static string $view = 'filament.pages.admin-task-hub';

    protected User $currentUser;

    protected bool $isSuperadmin = false;

    protected array $cards = [];

    protected array $panelSections = [];

    protected array $stats = [];
    protected const NAV_ROLES = ['superadmin', 'admin'];

    public function mount(): void
    {
        $user = auth()->user();
        abort_unless($user instanceof User, 403);
        abort_unless(static::canView(), 403);

        $this->currentUser = $user;
        $this->isSuperadmin = $this->userHasRole($user, ['superadmin']);
        $this->cards = $this->buildActionCards($user);
        $this->panelSections = $this->buildPanelSections($user);
        $this->stats = $this->buildStats($user);
    }

    protected function getViewData(): array
    {
        return [
            'user' => $this->currentUser,
            'cards' => $this->cards,
            'panelSections' => $this->panelSections,
            'stats' => $this->stats,
        ];
    }

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(self::NAV_ROLES) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canView();
    }

    /**
     * Build contextual shortcut cards.
     */
    protected function buildActionCards(User $user): array
    {
        $cards = [];

        $push = function (string $title, string $description, string $url, string $icon = '') use (&$cards): void {
            $cards[] = compact('title', 'description', 'url', 'icon');
        };

        if ($this->userHasRole($user, ['guru', 'wali_kelas'])) {
            $push('Input Setoran Hafalan', 'Catat setoran terbaru untuk santri binaan Anda.', route('guru.setoran.index'), '');
            $push('Rekap Hafalan', 'Lihat progres santri per halaqoh.', route('guru.setoran.rekap'), '');
        }

        if (
            $this->userHasRole($user, ['koordinator_tahfizh_putra', 'koordinator_tahfizh_putri']) ||
            $this->userHasJabatan($user, ['koor_tahfizh_putra', 'koor_tahfizh_putri'])
        ) {
            $push('Kelola Halaqoh', 'Atur pengampu dan santri pada halaqoh tahfizh.', route('tahfizh.halaqoh.index'), '');
        }

        if ($this->userHasRole($user, ['wakamad_kurikulum', 'wakamad_kesiswaan', 'wakamad_sarpras'])) {
            $push('Kalender Pendidikan', 'Susun agenda akademik unit Anda.', route('filament.admin.resources.kalender-pendidikan.index'), '');
        }

        if ($this->userHasRole($user, ['bendahara'])) {
            $push('Input Laporan Keuangan', 'Lengkapi administrasi keuangan dan laporan rutin.', route('admin.laporan.index'), '');
        }

        if ($this->userHasRole($user, ['wali_santri'])) {
            $push('Pantau Hafalan Anak', 'Lihat progres hafalan dan catatan kesehatan.', route('wali.progres'), '');
            $push('Perbarui Profil Wali', 'Perbarui data kontak wali & santri.', route('wali.profil'), '');
        }

        if ($this->userHasRole($user, ['pimpinan', 'mudir_pondok', 'naibul_mudir', 'naibatul_mudir', 'kabag_kesantrian_putra', 'kabag_kesantrian_putri', 'kabag_umum'])) {
            $push('Dashboard Pimpinan', 'Akses ringkasan unit dan pondok.', route('pimpinan.dashboard'), '');
        }

        if (
            $this->userHasRole($user, ['superadmin', 'admin', 'admin_unit']) ||
            $this->userHasJabatan($user, ['admin_unit'])
        ) {
            $push('Masuk Control Panel (Filament)', 'Kelola data guru, santri, dan jabatan di control panel.', url('/filament'), '');
        }

        return $cards;
    }

    /**
     * Build per-role quick access catalog based on config/jabatan.php
     */
    protected function buildPanelSections(User $user): array
    {
        $catalog = config('jabatan.panels', []);
        if (empty($catalog)) {
            return [];
        }

        $sections = [];
        $canViewAll = $this->userHasRole($user, ['superadmin']);

        $resolveUrl = static function (array $entry): string {
            $route = $entry['route'] ?? null;
            if (is_array($route) && isset($route['name'])) {
                return route($route['name'], $route['params'] ?? []);
            }

            if (is_string($route) && $route !== '') {
                return route($route);
            }

            return '#';
        };

        foreach ($catalog as $category) {
            $items = [];

            foreach ($category['entries'] as $entry) {
                $roles = Arr::wrap($entry['roles'] ?? []);
                $hasAccess = $canViewAll;

                if (!$hasAccess && !empty($roles)) {
                    $hasAccess = $this->userHasRole($user, $roles) || $this->userHasJabatan($user, $roles);
                }

                if ($hasAccess) {
                    $items[] = [
                        'title' => $entry['title'],
                        'description' => $entry['description'] ?? '',
                        'url' => $resolveUrl($entry),
                    ];
                }
            }

            if ($items) {
                $sections[] = [
                    'label' => $category['label'],
                    'items' => $items,
                ];
            }
        }

        return $sections;
    }

    protected function buildStats(User $user): array
    {
        if ($this->isSuperadmin) {
            return [
                'totalSantri' => DB::table('santri')->count(),
                'totalGuru' => DB::table('guru')->count(),
                'totalHalaqoh' => DB::table('halaqoh')->count(),
                'totalUnits' => DB::table('units')->count(),
                'totalUsers' => DB::table('users')->count(),
            ];
        }

        $unitId = (int) ($user->unit_id ?? 0);
        $hasUnitScope = $this->hasUnitScope($user);
        $cacheKey = 'filament:task-hub:stats:' . ($hasUnitScope ? $unitId : 'all');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($hasUnitScope, $unitId) {
            $filterId = $hasUnitScope ? $unitId : null;

            return [
                'totalSantri' => DB::table('santri')->when($filterId, fn ($q) => $q->where('unit_id', $filterId))->count(),
                'totalGuru' => DB::table('guru')->when($filterId, fn ($q) => $q->where('unit_id', $filterId))->count(),
                'totalHalaqoh' => DB::table('halaqoh')->when($filterId, fn ($q) => $q->where('unit_id', $filterId))->count(),
            ];
        });
    }

    protected function hasUnitScope(User $user): bool
    {
        $unitId = (int) ($user->unit_id ?? 0);
        if ($unitId <= 0) {
            return false;
        }

        if ($this->userHasRole($user, ['superadmin'])) {
            return false;
        }

        $unitScopedRoles = [
            'admin',
            'admin_unit',
            'kepala_madrasah',
            'wakamad_kurikulum',
            'wakamad_kesiswaan',
            'wakamad_sarpras',
            'bendahara',
        ];

        return $this->userHasRole($user, $unitScopedRoles) ||
            $this->userHasJabatan($user, $unitScopedRoles);
    }

    protected function userHasRole(User $user, array $roles): bool
    {
        $roles = array_map('strtolower', Arr::wrap($roles));
        $legacyRole = strtolower($user->role ?? '');

        if ($legacyRole !== '' && in_array($legacyRole, $roles, true)) {
            return true;
        }

        if (method_exists($user, 'hasRole')) {
            return $user->hasRole($roles);
        }

        return false;
    }

    protected function userHasJabatan(User $user, array $roles): bool
    {
        if (!method_exists($user, 'hasJabatan')) {
            return false;
        }

        return $user->hasJabatan($roles);
    }
}
