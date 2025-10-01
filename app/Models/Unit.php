<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $table = 'units';

    protected $fillable = [
        'nama',
    ];

    /**
     * Relasi ke StaffEmployees
     */
    public function staffEmployees()
    {
        return $this->hasMany(StaffEmployee::class);
    }

    /**
     * Scope untuk search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', "%{$search}%");
    }
}
