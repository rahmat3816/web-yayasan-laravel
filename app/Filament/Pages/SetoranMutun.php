<?php

namespace App\Filament\Pages;

use App\Http\Controllers\Tahfizh\MutunSetoranPageController;
use App\Models\Halaqoh;
use App\Support\TahfizhMutun;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SetoranMutun extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';

    protected static ?string $navigationLabel = 'Setoran Mutun';

    protected static ?string $navigationGroup = 'Tahfizh Mutun';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'tahfizh/mutun/setoran';

    protected static string $view = 'filament.pages.setoran-mutun';

    protected array $data = [];

    public function mount(): void
    {
        if (! static::canView()) {
            $this->data = ['forbidden' => true];
            return;
        }

        $response = app(MutunSetoranPageController::class)->index(request());

        if ($response instanceof View) {
            $this->data = $response->getData();
        }
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), $this->data);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperadmin') && $user->isSuperadmin()) {
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

    public function getHeading(): string
    {
        return '';
    }
}
