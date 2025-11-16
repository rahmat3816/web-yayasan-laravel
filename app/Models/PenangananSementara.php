<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenangananSementara extends Model
{
    use HasFactory;

    protected $table = 'penanganan_sementara';

    protected $fillable = [
        'nama',
        'slug',
        'urutan',
    ];
}
