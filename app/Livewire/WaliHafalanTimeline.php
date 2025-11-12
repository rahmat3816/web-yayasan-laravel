<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Models\WaliSantri;
use App\Models\HafalanQuran;

class WaliHafalanTimeline extends Component
{
    public ?int $selectedSantriId = null;
    public array $timeline = [
        'labels' => [],
        'datasets' => [],
    ];

    public function render()
    {
        $user = Auth::user();
        $santriList = WaliSantri::with('santri:id,nama')
            ->where('user_id', $user->id)
            ->get()
            ->pluck('santri')
            ->filter()
            ->values();

        if ($santriList->isEmpty()) {
            $this->timeline = [
                'labels' => [],
                'datasets' => [],
            ];
            return view('livewire.wali-hafalan-timeline', [
                'santriList' => collect(),
                'selectedSantriId' => null,
                'timeline' => $this->timeline,
            ]);
        }

        if (!$this->selectedSantriId || ! $santriList->contains('id', $this->selectedSantriId)) {
            $this->selectedSantriId = $santriList->first()->id;
        }

        $records = HafalanQuran::where('santri_id', $this->selectedSantriId)
            ->orderBy('tanggal_setor')
            ->get(['tanggal_setor', 'ayah_start', 'ayah_end']);

        $total = 0;
        $labels = [];
        $data = [];
        foreach ($records as $record) {
            $total += max(0, ($record->ayah_end - $record->ayah_start + 1));
            $labels[] = Carbon::parse($record->tanggal_setor)->format('Y-m-d');
            $data[] = $total;
        }

        $this->timeline = [
            'labels' => $labels,
            'datasets' => [[
                'label' => $santriList->firstWhere('id', $this->selectedSantriId)->nama ?? 'Santri',
                'data' => $data,
                'borderColor' => '#4f46e5',
                'backgroundColor' => '#4f46e5',
                'tension' => 0.3,
                'fill' => false,
            ]],
        ];

        return view('livewire.wali-hafalan-timeline', [
            'santriList' => $santriList,
            'selectedSantriId' => $this->selectedSantriId,
            'timeline' => $this->timeline,
        ]);
    }
}
