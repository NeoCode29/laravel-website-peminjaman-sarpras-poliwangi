<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriSarana extends Model
{
    use HasFactory;

    protected $table = 'kategori_sarana';

    protected $fillable = [
        'name',
        'description',
        'icon',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke sarana
     */
    public function sarana(): HasMany
    {
        return $this->hasMany(Sarana::class, 'kategori_id');
    }
}
