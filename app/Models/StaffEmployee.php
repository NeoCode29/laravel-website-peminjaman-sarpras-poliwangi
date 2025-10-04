<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'unit_id',
        'position_id',
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Unit
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Relasi ke Position
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Scope untuk filter berdasarkan unit
     */
    public function scopeByUnit($query, $unitId)
    {
        return $query->where('unit_id', $unitId);
    }

    /**
     * Scope untuk filter berdasarkan position
     */
    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    /**
     * Scope untuk search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nip', 'like', "%{$search}%")
                    ->orWhereHas('user', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                          ->orWhere('username', 'like', "%{$search}%");
                    });
    }
}
