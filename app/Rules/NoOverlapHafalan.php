<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\HafalanQuran;

class NoOverlapHafalan implements ValidationRule
{
    protected int $santriId;
    protected string $mode;
    protected ?int $pStart;
    protected ?int $pEnd;
    protected ?int $surahId;
    protected ?int $aStart;
    protected ?int $aEnd;

    public function __construct(int $santriId, string $mode, ?int $pStart=null, ?int $pEnd=null, ?int $surahId=null, ?int $aStart=null, ?int $aEnd=null)
    {
        $this->santriId = $santriId;
        $this->mode     = $mode;
        $this->pStart   = $pStart;
        $this->pEnd     = $pEnd;
        $this->surahId  = $surahId;
        $this->aStart   = $aStart;
        $this->aEnd     = $aEnd;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $q = HafalanQuran::where('santri_id', $this->santriId)
            ->where('mode', $this->mode);

        if ($this->mode === 'page') {
            // Overlap jika: startA <= endB && endA >= startB
            $overlap = $q->where(function($w){
                $w->whereNotNull('page_start')
                  ->whereNotNull('page_end')
                  ->where('page_start', '<=', $this->pEnd)
                  ->where('page_end', '>=', $this->pStart);
            })->exists();

            if ($overlap) {
                $fail('Rentang halaman mushaf yang dipilih sudah pernah disetorkan oleh santri ini.');
            }
        } else {
            // mode 'ayat' → cek overlap di surah yang sama
            $overlap = $q->where('surah_id', $this->surahId)
                ->where(function($w){
                    $w->whereNotNull('ayah_start')
                      ->whereNotNull('ayah_end')
                      ->where('ayah_start', '<=', $this->aEnd)
                      ->where('ayah_end', '>=', $this->aStart);
                })->exists();

            if ($overlap) {
                $fail('Rentang ayat pada surah tersebut sudah pernah disetorkan oleh santri ini.');
            }
        }
    }
}
