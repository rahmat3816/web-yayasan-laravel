<?php

namespace App\Filament\Pages;

use App\Models\KetaatanType;
use App\Models\Santri;
use App\Services\Keamanan\KetaatanService;
use App\Support\KeamananAccess;
use Filament\Pages\Page;
use Illuminate\Http\Request;

class CatatKetaatan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Catat Ketaatan';

    protected static ?string $navigationGroup = 'Keamanan';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'keamanan/catat-ketaatan';

    protected static string $view = 'filament.pages.catat-ketaatan';

    public array $payload = [];

    public function mount(Request $request): void
    {
        abort_unless(KeamananAccess::userHasAccess(auth()->user()), 403);

        $santriIds = KeamananAccess::accessibleSantriIds(auth()->user());

        $santriOptions = Santri::query()
            ->select('id', 'nama')
            ->when(!KeamananAccess::userHasFullSantriScope(auth()->user()), fn ($q) => $q->whereIn('id', $santriIds ?: [-1]))
            ->orderBy('nama')
            ->get();

        $ketaatanOptions = KetaatanType::query()
            ->where('aktif', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'poin_pengurang']);

        $this->payload = [
            'santriOptions' => $santriOptions,
            'ketaatanOptions' => $ketaatanOptions,
            'defaultSantriId' => $request->query('santri_id') ?: ($santriOptions->first()->id ?? null),
        ];
    }

    public function submit(): void
    {
        $data = request()->validate([
            'santri_id' => ['required', 'exists:santri,id'],
            'ketaatan_type_id' => ['required', 'exists:ketaatan_types,id'],
            'poin' => ['nullable', 'integer', 'min:0'],
            'catatan' => ['nullable', 'string'],
        ]);

        abort_unless(KeamananAccess::userHasAccess(auth()->user()), 403);

        (new KetaatanService())->catat(array_merge($data, [
            'dibuat_oleh' => auth()->id(),
        ]));

        session()->flash('success', 'Ketaatan berhasil dicatat dan poin pelanggaran dikurangi.');
        $this->redirect(route('filament.admin.pages.keamanan.catat-ketaatan'));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return KeamananAccess::userHasAccess(auth()->user());
    }

    public static function canAccess(): bool
    {
        return KeamananAccess::userHasAccess(auth()->user());
    }

    public static function canView(): bool
    {
        return KeamananAccess::userHasAccess(auth()->user());
    }

    public function getHeading(): string
    {
        return '';
    }

    protected function getViewData(): array
    {
        return $this->payload;
    }
}
