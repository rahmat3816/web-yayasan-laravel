<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asrama extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'tipe',
        'lokasi',
        'keterangan',
    ];

    protected $casts = [
        'tipe' => 'string',
    ];

    public function santri()
    {
        return $this->hasMany(Santri::class, 'asrama_id');
    }

    public function musyrifAssignments()
    {
        return $this->hasMany(MusyrifAssignment::class, 'asrama_id');
    }

    public function healthLogs()
    {
        return $this->hasMany(SantriHealthLog::class, 'asrama_id');
    }
}
