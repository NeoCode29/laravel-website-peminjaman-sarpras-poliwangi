<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nim',
        'angkatan',
        'jurusan_id',
        'prodi_id',
        'semester',
        'status_mahasiswa',
    ];

    protected $casts = [
        'angkatan' => 'integer',
        'semester' => 'integer',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Jurusan
     */
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    /**
     * Relasi ke Prodi
     */
    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    /**
     * Scope untuk filter berdasarkan status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status_mahasiswa', $status);
    }

    /**
     * Scope untuk filter berdasarkan angkatan
     */
    public function scopeByAngkatan($query, $angkatan)
    {
        return $query->where('angkatan', $angkatan);
    }

    /**
     * Scope untuk filter berdasarkan jurusan
     */
    public function scopeByJurusan($query, $jurusanId)
    {
        return $query->where('jurusan_id', $jurusanId);
    }

    /**
     * Scope untuk filter berdasarkan prodi
     */
    public function scopeByProdi($query, $prodiId)
    {
        return $query->where('prodi_id', $prodiId);
    }

    /**
     * Scope untuk search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nim', 'like', "%{$search}%")
                    ->orWhereHas('user', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('username', 'like', "%{$search}%");
                    });
    }

    /**
     * Get status display name
     */
    public function getStatusDisplayAttribute()
    {
        $statuses = [
            'aktif' => 'Aktif',
            'cuti' => 'Cuti',
            'dropout' => 'Drop Out',
            'lulus' => 'Lulus'
        ];
        
        return $statuses[$this->status_mahasiswa] ?? $this->status_mahasiswa;
    }
}
