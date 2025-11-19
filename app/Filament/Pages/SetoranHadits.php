<?php

namespace App\Filament\Pages;

use App\Http\Controllers\Tahfizh\HaditsSetoranPageController;
use App\Models\Halaqoh;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SetoranHadits extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $navigationLabel = 'Setoran Hadits';

    protected static ?string $navigationGroup = 'Tahfizh Hadits';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'tahfizh/hadits/setoran';

    protected static string $view = 'filament.pages.setoran-hadits';

    protected array $data = [];
    public function mount(): void
    {
        if (! static::canView()) {
            $this->data = ['forbidden' => true];
            return;
        }

        $response = app(HaditsSetoranPageController::class)->index(request());

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

        if (! $user) {
            return false;
        }

        if ($user->hasRole('superadmin')) {
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
