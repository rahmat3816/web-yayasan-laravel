<?php

namespace App\Filament\Pages;

use App\Models\PelanggaranType;
use App\Models\Santri;
use App\Services\Keamanan\PelanggaranService;
use App\Support\KeamananAccess;
use Filament\Pages\Page;
use Illuminate\Http\Request;
use App\Models\PelanggaranCategory;

class PencatatanPelanggaran extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?string $navigationLabel = 'Catat Pelanggaran';

    protected static ?string $navigationGroup = 'Keamanan';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'keamanan/catat-pelanggaran';

    protected static string $view = 'filament.pages.pencatatan-pelanggaran';

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

        $pelanggaranOptions = PelanggaranType::query()
            ->where('aktif', true)
            ->with('kategori:id,nama')
            ->orderBy('nama')
            ->get()
            ->map(fn ($type) => [
                'id' => $type->id,
                'nama' => $type->nama,
                'kategori' => $type->kategori?->nama,
                'kategori_id' => $type->kategori_id,
                'poin' => $type->poin_default,
            ]);

        $kategoriOptions = PelanggaranCategory::orderBy('nama')->get(['id', 'nama']);

        $this->payload = [
            'santriOptions' => $santriOptions,
            'pelanggaranOptions' => $pelanggaranOptions,
            'defaultSantriId' => $request->query('santri_id') ?: ($santriOptions->first()->id ?? null),
            'kategoriOptions' => $kategoriOptions,
        ];
    }

    public function submit(): void
    {
        $data = request()->validate([
            'santri_id' => ['required', 'exists:santri,id'],
            'pelanggaran_type_id' => ['required', 'exists:pelanggaran_types,id'],
            'poin' => ['nullable', 'integer', 'min:0'],
            'catatan' => ['nullable', 'string'],
            'tanggal' => ['required', 'date'],
        ]);

        abort_unless(KeamananAccess::userHasAccess(auth()->user()), 403);

        (new PelanggaranService())->catat(array_merge($data, [
            'dibuat_oleh' => auth()->id(),
            'created_at' => $data['tanggal'],
        ]));

        session()->flash('success', 'Pelanggaran berhasil dicatat.');
        $this->redirect(route('filament.admin.pages.keamanan.catat-pelanggaran'));
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
