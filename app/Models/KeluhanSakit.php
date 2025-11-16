<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KeluhanSakit extends Model
{
    use HasFactory;

    protected $table = 'keluhan_sakit';

    protected $fillable = [
        'nama',
        'slug',
        'urutan',
    ];
}
