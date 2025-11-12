<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiMapel extends Model
{
    protected $table = 'absensi_mapel';
    protected $fillable = ['santri_id', 'mapel_id', 'guru_id', 'unit_id', 'tanggal', 'status', 'keterangan'];

    public function santri()
    {
        return $this->belongsTo(Santri::class, 'santri_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
