<?php

namespace App\Livewire;

use Livewire\Component;

class DashboardStats extends Component
{
    public array $metrics = [];

    public function mount(array $metrics = []): void
    {
        $this->metrics = $metrics;
    }

    public function render()
    {
        $defaults = [
            'totalSantri' => 0,
            'totalGuru' => 0,
            'totalHalaqoh' => 0,
            'totalUnits' => null,
            'totalUsers' => null,
        ];

        $metrics = array_merge($defaults, $this->metrics);

        $cards = [
            [
                'label' => 'Total Santri',
                'value' => number_format($metrics['totalSantri']),
                'icon' => 'heroicon-o-academic-cap',
                'gradient' => 'from-sky-500/90 via-sky-400/90 to-cyan-400/90',
            ],
            [
                'label' => 'Total Guru',
                'value' => number_format($metrics['totalGuru']),
                'icon' => 'heroicon-o-user-group',
                'gradient' => 'from-violet-500/90 via-violet-400/90 to-indigo-400/90',
            ],
            [
                'label' => 'Total Halaqoh',
                'value' => number_format($metrics['totalHalaqoh']),
                'icon' => 'heroicon-o-rectangle-stack',
                'gradient' => 'from-emerald-500/90 via-teal-400/90 to-lime-400/90',
            ],
        ];

        if (!is_null($metrics['totalUnits'])) {
            $cards[] = [
                'label' => 'Unit Pendidikan',
                'value' => number_format($metrics['totalUnits']),
                'icon' => 'heroicon-o-building-library',
                'gradient' => 'from-amber-500/90 via-orange-400/90 to-yellow-400/90',
            ];
        }

        if (!is_null($metrics['totalUsers'])) {
            $cards[] = [
                'label' => 'Akun Terdaftar',
                'value' => number_format($metrics['totalUsers']),
                'icon' => 'heroicon-o-user-circle',
                'gradient' => 'from-rose-500/90 via-pink-400/90 to-fuchsia-400/90',
            ];
        }

        return view('livewire.dashboard-stats', [
            'cards' => $cards,
        ]);
    }
}
