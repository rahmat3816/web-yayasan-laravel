<?php

namespace Database\Seeders;

use App\Models\Asrama;
use App\Models\Guru;
use App\Models\MusyrifAssignment;
use Illuminate\Database\Seeder;

class MusyrifAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $assignments = [
            ['guru_nama' => 'Musyrif Putra 1', 'asrama_nama' => 'Asrama Ibn Taimiyah', 'gender' => 'L'],
            ['guru_nama' => 'Musyrif Putra 2', 'asrama_nama' => 'Asrama Ibn Katsir', 'gender' => 'L'],
            ['guru_nama' => 'Musyrifah Putri 1', 'asrama_nama' => 'Asrama Aisyah', 'gender' => 'P'],
            ['guru_nama' => 'Musyrifah Putri 2', 'asrama_nama' => 'Asrama Fatimah', 'gender' => 'P'],
        ];

        foreach ($assignments as $assignment) {
            $guru = Guru::firstOrCreate(
                ['nama' => $assignment['guru_nama']],
                [
                    'jenis_kelamin' => $assignment['gender'],
                    'tanggal_bergabung' => now()->subMonths(6)->toDateString(),
                ]
            );

            $tipeAsrama = $assignment['gender'] === 'L' ? 'putra' : 'putri';
            $asrama = Asrama::firstOrCreate(
                ['nama' => $assignment['asrama_nama']],
                [
                    'tipe' => $tipeAsrama,
                    'lokasi' => $tipeAsrama === 'putra' ? 'Komplek Putra' : 'Komplek Putri',
                ]
            );

            MusyrifAssignment::firstOrCreate(
                [
                    'guru_id' => $guru->id,
                    'asrama_id' => $asrama->id,
                    'mulai_tugas' => now()->subDays(5)->toDateString(),
                ],
                [
                    'status' => 'aktif',
                    'catatan' => 'Seeder auto generated',
                ]
            );
        }
    }
}
