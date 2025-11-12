<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Jabatan extends Model
{
    use HasFactory;

    protected $table = 'jabatan';

    protected $fillable = [
        'nama_jabatan',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (Jabatan $jabatan) {
            if (filled($jabatan->slug)) {
                return;
            }

            $base = Str::slug($jabatan->nama_jabatan ?? 'jabatan');
            $slug = $base;
            $suffix = 1;

            while (static::where('slug', $slug)->exists()) {
                $slug = "{$base}-{$suffix}";
                $suffix++;
            }

            $jabatan->slug = $slug;
        });
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'guru_jabatan', 'jabatan_id', 'user_id')
            ->withPivot('unit_id');
    }

    public function guruAssignments()
    {
        return $this->hasMany(GuruJabatan::class, 'jabatan_id');
    }
}
