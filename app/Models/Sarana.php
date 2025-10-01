<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sarana extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'kategori_id',
        'type',
        'jumlah_total',
        'description',
        'image_url',
        'lokasi',
        'jumlah_tersedia',
        'jumlah_rusak',
        'jumlah_maintenance',
        'jumlah_hilang',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke kategori sarana
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSarana::class, 'kategori_id');
    }

    /**
     * Relasi ke user yang membuat
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke unit sarana (untuk tipe serialized)
     */
    public function units(): HasMany
    {
        return $this->hasMany(SaranaUnit::class, 'sarana_id');
    }

    /**
     * Scope untuk sarana serialized
     */
    public function scopeSerialized($query)
    {
        return $query->where('type', 'serialized');
    }

    /**
     * Scope untuk sarana pooled
     */
    public function scopePooled($query)
    {
        return $query->where('type', 'pooled');
    }

    /**
     * Hitung statistik untuk sarana serialized
     */
    public function calculateSerializedStats()
    {
        if ($this->type !== 'serialized') {
            return;
        }

        $this->jumlah_total = $this->units()->count();
        $this->jumlah_tersedia = $this->units()->where('unit_status', 'tersedia')->count();
        $this->jumlah_rusak = $this->units()->where('unit_status', 'rusak')->count();
        $this->jumlah_maintenance = $this->units()->where('unit_status', 'maintenance')->count();
        $this->jumlah_hilang = $this->units()->where('unit_status', 'hilang')->count();
        $this->save();
    }

    /**
     * Hitung ketersediaan untuk sarana pooled
     */
    public function calculatePooledAvailability()
    {
        if ($this->type !== 'pooled') {
            return;
        }

        // Hitung qty yang sedang dipinjam aktif
        $qtyDipinjamAktif = \DB::table('peminjaman_items')
            ->join('peminjaman', 'peminjaman_items.peminjaman_id', '=', 'peminjaman.id')
            ->where('peminjaman_items.sarana_id', $this->id)
            ->whereIn('peminjaman.status', ['approved', 'picked_up'])
            ->sum('peminjaman_items.qty_approved');

        $this->jumlah_tersedia = $this->jumlah_total - 
            ($this->jumlah_rusak + $this->jumlah_maintenance + $this->jumlah_hilang) - 
            $qtyDipinjamAktif;
        $this->save();
    }

    /**
     * Update statistik otomatis
     */
    public function updateStats()
    {
        if ($this->type === 'serialized') {
            $this->calculateSerializedStats();
        } else {
            $this->calculatePooledAvailability();
        }
    }
}
