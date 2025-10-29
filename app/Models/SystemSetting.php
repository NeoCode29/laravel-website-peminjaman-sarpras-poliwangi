<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get setting value by key
     */
    public static function getValue(string $key, $default = null)
    {
        // Cached fetch (TTL 3600s) per PRD requirement
        $cacheKey = 'system_setting_' . $key;
        $value = cache()->remember($cacheKey, 3600, function () use ($key) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : null;
        });
        return $value !== null ? $value : $default;
    }

    /**
     * Set setting value by key
     */
    public static function setValue(string $key, $value, string $description = null): bool
    {
        $result = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description
            ]
        ) ? true : false;

        // Invalidate cache for this key
        cache()->forget('system_setting_' . $key);
        return $result;
    }

    /**
     * Get all settings as key-value array
     */
    public static function getAllAsArray(): array
    {
        // Cache full map for fast access; bust when any setting changes externally
        return cache()->remember('system_settings_all', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get settings by category
     */
    public static function getByCategory(string $category): array
    {
        return static::where('key', 'like', $category . '%')
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Check if setting exists
     */
    public static function exists(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Delete setting by key
     */
    public static function deleteByKey(string $key): bool
    {
        return static::where('key', $key)->delete() > 0;
    }

    /**
     * Get default system settings
     */
    public static function getDefaultSettings(): array
    {
        return [
            'max_duration_days' => [
                'value' => '7',
                'description' => 'Durasi maksimal peminjaman (hari)'
            ],
            'event_gap_hours' => [
                'value' => '2',
                'description' => 'Jeda antar acara (jam)'
            ],
            'marking_duration_days' => [
                'value' => '3',
                'description' => 'Masa berlaku marking (hari)'
            ],
            'max_planned_submit_days' => [
                'value' => '30',
                'description' => 'Batas waktu submit pengajuan (hari)'
            ],
            'max_active_borrowings' => [
                'value' => '3',
                'description' => 'Batas kuota peminjaman aktif per user'
            ],
            'notifications_enabled' => [
                'value' => 'true',
                'description' => 'Enable/disable notifikasi in-web'
            ]
        ];
    }

    /**
     * Initialize default settings
     */
    public static function initializeDefaults(): void
    {
        $defaults = static::getDefaultSettings();
        
        foreach ($defaults as $key => $data) {
            if (!static::exists($key)) {
                static::create([
                    'key' => $key,
                    'value' => $data['value'],
                    'description' => $data['description']
                ]);
            }
        }
    }
}