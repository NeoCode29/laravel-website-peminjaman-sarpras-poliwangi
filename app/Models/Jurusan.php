<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    protected $table = 'jurusan';

    protected $fillable = [
        'nama_jurusan',
        'deskripsi',
    ];

    /**
     * Relasi ke Prodi
     */
    public function prodis()
    {
        return $this->hasMany(Prodi::class);
    }

    /**
     * Relasi ke Students
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Scope untuk search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama_jurusan', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
    }
}
