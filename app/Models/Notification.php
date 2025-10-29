<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'action_url',
        'is_clickable',
        'read_at',
        'expires_at',
    ];

    protected $casts = [
        'is_clickable' => 'boolean',
        'read_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Relasi ke user penerima notifikasi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: hanya yang belum dibaca
     */
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    /**
     * Scope: tidak expired
     */
    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
        });
    }
}



