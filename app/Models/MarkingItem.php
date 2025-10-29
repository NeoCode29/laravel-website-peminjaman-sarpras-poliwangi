<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarkingItem extends Model
{
    use HasFactory;

    protected $table = 'marking_items';

    protected $fillable = [
        'marking_id',
        'sarana_id',
    ];

    /**
     * Get the marking that owns this item
     */
    public function marking(): BelongsTo
    {
        return $this->belongsTo(Marking::class);
    }

    /**
     * Get the sarana for this item
     */
    public function sarana(): BelongsTo
    {
        return $this->belongsTo(Sarana::class);
    }

    /**
     * Get the user through marking
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')
            ->through('marking');
    }
}
