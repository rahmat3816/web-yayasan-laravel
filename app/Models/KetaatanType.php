<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KetaatanType extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'poin_pengurang',
        'catatan',
        'aktif',
    ];
}
