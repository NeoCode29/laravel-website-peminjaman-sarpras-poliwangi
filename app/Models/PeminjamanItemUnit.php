<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeminjamanItemUnit extends Model
{
    use HasFactory;

    protected $table = 'peminjaman_item_units';

    protected $fillable = [
        'peminjaman_id',
        'peminjaman_item_id',
        'unit_id',
        'assigned_by',
        'assigned_at',
        'status',
        'released_by',
        'released_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    /**
     * Get the peminjaman that owns this unit
     */
    public function peminjamanItem(): BelongsTo
    {
        return $this->belongsTo(PeminjamanItem::class, 'peminjaman_item_id');
    }

    /**
     * Get the sarana unit that was assigned
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(SaranaUnit::class, 'unit_id');
    }

    /**
     * Get the user who assigned this unit
     */
    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the user who released this unit
     */
    public function releasedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    /**
     * Get the peminjaman through peminjamanItem
     */
    public function peminjaman()
    {
        return $this->hasOneThrough(Peminjaman::class, PeminjamanItem::class, 'id', 'id', 'peminjaman_item_id', 'peminjaman_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
