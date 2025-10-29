<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserQuota extends Model
{
    use HasFactory;

    protected $table = 'user_quotas';

    protected $fillable = [
        'user_id',
        'max_active_borrowings',
        'current_borrowings',
    ];

    protected $casts = [
        'max_active_borrowings' => 'integer',
        'current_borrowings' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getOrCreateForUser(int $userId): self
    {
        return static::firstOrCreate(
            ['user_id' => $userId],
            [
                'max_active_borrowings' => (int) SystemSetting::getValue('max_active_borrowings', 3),
                'current_borrowings' => 0,
            ]
        );
    }
}



