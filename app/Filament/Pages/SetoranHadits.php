<?php

namespace App\Filament\Pages;

use App\Http\Controllers\Tahfizh\HaditsSetoranPageController;
use App\Support\TahfizhHadits;
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

        if (TahfizhHadits::userHasAccess($user)) {
            return true;
        }

        return ! empty(TahfizhHadits::accessibleSantriIds($user));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canView();
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }
}
