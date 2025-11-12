<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'mapel';

    protected $fillable = [
        'nama',
        'tipe',
    ];

    protected $casts = [
        'tipe' => 'string',
    ];

    public function guruMapel()
    {
        return $this->hasMany(GuruMapel::class, 'mapel_id');
    }
}

