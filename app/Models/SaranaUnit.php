<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaranaUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'sarana_id',
        'unit_code',
        'unit_status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke sarana
     */
    public function sarana(): BelongsTo
    {
        return $this->belongsTo(Sarana::class, 'sarana_id');
    }

    /**
     * Scope untuk unit tersedia
     */
    public function scopeTersedia($query)
    {
        return $query->where('unit_status', 'tersedia');
    }

    /**
     * Scope untuk unit rusak
     */
    public function scopeRusak($query)
    {
        return $query->where('unit_status', 'rusak');
    }

    /**
     * Scope untuk unit maintenance
     */
    public function scopeMaintenance($query)
    {
        return $query->where('unit_status', 'maintenance');
    }

    /**
     * Scope untuk unit hilang
     */
    public function scopeHilang($query)
    {
        return $query->where('unit_status', 'hilang');
    }

    /**
     * Update status unit dan recalculate sarana stats
     */
    public function updateStatus($status)
    {
        $this->unit_status = $status;
        $this->save();
        
        // Update statistik sarana
        $this->sarana->updateStats();
    }
}
