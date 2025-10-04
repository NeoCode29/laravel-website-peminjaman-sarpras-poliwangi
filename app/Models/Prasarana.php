<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prasarana extends Model
{
    use HasFactory;

    protected $table = 'prasarana';

    protected $fillable = [
        'name',
        'kategori_id',
        'description',
        'status',
        'kapasitas',
        'lokasi',
        'created_by',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPrasarana::class, 'kategori_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PrasaranaImage::class, 'prasarana_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}



