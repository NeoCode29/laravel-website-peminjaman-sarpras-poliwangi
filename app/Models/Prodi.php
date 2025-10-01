<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prodi extends Model
{
    use HasFactory;

    protected $table = 'prodi';

    protected $fillable = [
        'nama_prodi',
        'jurusan_id',
        'jenjang',
        'deskripsi',
    ];

    /**
     * Relasi ke Jurusan
     */
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
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
        return $query->where('nama_prodi', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
    }

    /**
     * Scope untuk filter berdasarkan jurusan
     */
    public function scopeByJurusan($query, $jurusanId)
    {
        return $query->where('jurusan_id', $jurusanId);
    }

    /**
     * Scope untuk filter berdasarkan jenjang
     */
    public function scopeByJenjang($query, $jenjang)
    {
        return $query->where('jenjang', $jenjang);
    }
}
