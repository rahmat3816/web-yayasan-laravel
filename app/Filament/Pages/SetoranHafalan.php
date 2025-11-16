<?php

namespace App\Filament\Pages;

use App\Http\Controllers\Guru\SetoranHafalanController;
use App\Models\Halaqoh;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SetoranHafalan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Setoran Hafalan';
    protected static ?string $navigationGroup = 'Tahfizh';
    protected static ?int $navigationSort = 20;
    protected static ?string $slug = 'tahfizh/setoran-hafalan';
    protected static string $view = 'filament.pages.setoran-hafalan';

    protected array $data = [];
    protected const PRIVILEGED_ROLES = [
        'superadmin',
        'kabag_kesantrian_putra',
        'kabag_kesantrian_putri',
        'koordinator_tahfizh_putra',
        'koordinator_tahfizh_putri',
        'koor_tahfizh_putra',
        'koor_tahfizh_putri',
    ];

    public function mount(): void
    {
        if (! static::canView()) {
            $this->data = ['forbidden' => true];
            return;
        }

        $controller = app(SetoranHafalanController::class);
        $response = $controller->index(request());

        if ($response instanceof View) {
            $this->data = $response->getData();
        }
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), $this->data);
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if ($user->hasAnyRole(self::PRIVILEGED_ROLES)) {
            return true;
        }

        return static::guruIsPengampu($user);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canView();
    }

    protected static function guruIsPengampu($user): bool
    {
        if (! $user?->hasRole('guru')) {
            return false;
        }

        $guruId = $user->linked_guru_id ?? $user->guru?->id;

        if (! $guruId && method_exists($user, 'ensureLinkedGuruId')) {
            $guruId = $user->ensureLinkedGuruId($user->name);
        }

        if (! $guruId) {
            return false;
        }

        return Halaqoh::where('guru_id', $guruId)->exists();
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }
}
