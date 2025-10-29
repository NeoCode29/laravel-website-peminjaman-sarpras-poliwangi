<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrasaranaImage extends Model
{
    use HasFactory;

    protected $table = 'prasarana_images';

    protected $fillable = [
        'prasarana_id',
        'image_url',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the prasarana that owns the image.
     */
    public function prasarana(): BelongsTo
    {
        return $this->belongsTo(Prasarana::class);
    }

    /**
     * Get the full image URL.
     */
    public function getFullImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_url);
    }

    /**
     * Get the image path for storage.
     */
    public function getImagePathAttribute(): string
    {
        return storage_path('app/public/' . $this->image_url);
    }

    /**
     * Check if image exists in storage.
     */
    public function imageExists(): bool
    {
        return file_exists($this->image_path);
    }

    /**
     * Scope for ordered images.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}