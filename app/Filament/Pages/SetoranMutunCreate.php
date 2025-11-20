<?php

namespace App\Filament\Pages;

use App\Http\Controllers\Tahfizh\MutunSetoranFormController;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SetoranMutunCreate extends Page
{
    protected static ?string $navigationIcon = null;

    protected static ?string $navigationLabel = null;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'mutun-setorans/create';

    protected static string $view = 'filament.pages.setoran-mutun-create';

    protected array $data = [];

    public function mount(Request $request): void
    {
        abort_unless(Auth::check(), 403);

        $response = app(MutunSetoranFormController::class)->create($request);

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
        return Auth::check();
    }

    public function getHeading(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return '';
    }
}
