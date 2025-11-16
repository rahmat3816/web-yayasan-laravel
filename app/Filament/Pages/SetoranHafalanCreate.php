<?php

namespace App\Filament\Pages;

use App\Http\Controllers\Guru\SetoranHafalanController;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SetoranHafalanCreate extends Page
{
    protected static ?string $navigationIcon = null;
    protected static ?string $navigationLabel = null;
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.setoran-hafalan-create';

    public array $data = [];

    public function mount(Request $request, int $santri): void
    {
        abort_unless(static::canView(), 403);

        $controller = app(SetoranHafalanController::class);
        $response = $controller->create($santri);

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

    public static function canView(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        return $user->hasAnyRole([
            'superadmin',
            'guru',
            'koordinator_tahfizh_putra',
            'koordinator_tahfizh_putri',
            'koor_tahfizh_putra',
            'koor_tahfizh_putri',
        ]);
    }
}
