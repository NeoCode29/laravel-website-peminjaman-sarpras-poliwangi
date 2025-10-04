<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $table = 'positions';

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
