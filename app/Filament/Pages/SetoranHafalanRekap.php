<?php

namespace App\Filament\Pages;

use App\Http\Controllers\Guru\SetoranHafalanController;
use App\Models\Halaqoh;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SetoranHafalanRekap extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Rekap Setoran';

    protected static ?string $navigationGroup = 'Tahfizh';

    protected static ?int $navigationSort = 25;

    protected static ?string $slug = 'tahfizh/setoran-hafalan/rekap';

    protected static string $view = 'filament.pages.setoran-hafalan-rekap';
    protected const PRIVILEGED_ROLES = [
        'superadmin',
        'kabag_kesantrian_putra',
        'kabag_kesantrian_putri',
        'koordinator_tahfizh_putra',
        'koordinator_tahfizh_putri',
    ];

    protected array $data = [];

    public function mount(Request $request): void
    {
        abort_unless(static::canView(), 403);

        $controller = app(SetoranHafalanController::class);
        $response = $controller->rekap($request);

        if ($response instanceof View) {
            $this->data = $response->getData();
        }
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), $this->data);
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canView();
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(self::PRIVILEGED_ROLES)) {
            return true;
        }

        return static::guruIsPengampu($user);
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
}
