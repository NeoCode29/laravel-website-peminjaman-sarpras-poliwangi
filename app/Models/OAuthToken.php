<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OAuthToken extends Model
{
    use HasFactory;

    protected $table = 'oauth_tokens';

    protected $fillable = [
        'user_id',
        'access_token',
        'refresh_token',
        'expires_in',
        'token_type',
        'scope',
    ];

    protected $casts = [
        'expires_in' => 'integer',
    ];

    /**
     * Get the user that owns the token.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if token is valid (not expired).
     */
    public function isValid()
    {
        // Token valid jika sekarang belum melewati created_at + expires_in
        if (!$this->created_at || !$this->expires_in) {
            return false;
        }
        return now()->lessThan($this->created_at->copy()->addSeconds($this->expires_in));
    }

    /**
     * Get expiration date.
     */
    public function getExpiresAtAttribute()
    {
        return $this->created_at->addSeconds($this->expires_in);
    }
}