<?php

namespace App\Helpers;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SystemSettingHelper
{
    const CACHE_PREFIX = 'system_setting_';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get system setting value with caching
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember(
            self::CACHE_PREFIX . $key,
            self::CACHE_TTL,
            function () use ($key, $default) {
                return SystemSetting::getValue($key, $default);
            }
        );
    }

    /**
     * Get max duration days setting
     */
    public static function getMaxDurationDays(): int
    {
        return (int) self::get('max_duration_days', 7);
    }

    /**
     * Get event gap hours setting
     */
    public static function getEventGapHours(): int
    {
        return (int) self::get('event_gap_hours', 2);
    }

    /**
     * Get marking duration days setting
     */
    public static function getMarkingDurationDays(): int
    {
        return (int) self::get('marking_duration_days', 3);
    }

    /**
     * Get max planned submit days setting
     */
    public static function getMaxPlannedSubmitDays(): int
    {
        return (int) self::get('max_planned_submit_days', 30);
    }

    /**
     * Get max active borrowings setting
     */
    public static function getMaxActiveBorrowings(): int
    {
        return (int) self::get('max_active_borrowings', 3);
    }

    /**
     * Get notifications enabled setting
     */
    public static function isNotificationsEnabled(): bool
    {
        return filter_var(self::get('notifications_enabled', 'true'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Get all system settings as array
     */
    public static function getAll(): array
    {
        return Cache::remember('all_system_settings', self::CACHE_TTL, function () {
            return SystemSetting::getAllAsArray();
        });
    }

    /**
     * Clear cache for specific setting
     */
    public static function clearCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Clear all system settings cache
     */
    public static function clearAllCache(): void
    {
        Cache::forget('all_system_settings');
        
        // Clear individual setting caches
        $keys = SystemSetting::pluck('key');
        foreach ($keys as $key) {
            self::clearCache($key);
        }
    }

    /**
     * Check if user can borrow based on current settings
     */
    public static function canUserBorrow(int $currentBorrowings): bool
    {
        $maxBorrowings = self::getMaxActiveBorrowings();
        return $currentBorrowings < $maxBorrowings;
    }

    /**
     * Get remaining borrowings for user
     */
    public static function getRemainingBorrowings(int $currentBorrowings): int
    {
        $maxBorrowings = self::getMaxActiveBorrowings();
        return max(0, $maxBorrowings - $currentBorrowings);
    }

    /**
     * Check if marking is allowed based on settings
     */
    public static function isMarkingAllowed(): bool
    {
        return self::isNotificationsEnabled(); // Marking depends on notifications
    }

    /**
     * Get marking expiration date
     */
    public static function getMarkingExpirationDate(): \Carbon\Carbon
    {
        $duration = self::getMarkingDurationDays();
        return now()->addDays($duration);
    }

    /**
     * Check if planned submit is within allowed timeframe
     */
    public static function isPlannedSubmitValid(\Carbon\Carbon $plannedDate): bool
    {
        $maxDays = self::getMaxPlannedSubmitDays();
        $maxDate = now()->addDays($maxDays);
        
        return $plannedDate->lte($maxDate);
    }

    /**
     * Get system settings for frontend
     */
    public static function getFrontendSettings(): array
    {
        return [
            'max_duration_days' => self::getMaxDurationDays(),
            'event_gap_hours' => self::getEventGapHours(),
            'marking_duration_days' => self::getMarkingDurationDays(),
            'max_planned_submit_days' => self::getMaxPlannedSubmitDays(),
            'max_active_borrowings' => self::getMaxActiveBorrowings(),
            'notifications_enabled' => self::isNotificationsEnabled(),
            'marking_allowed' => self::isMarkingAllowed(),
        ];
    }

    /**
     * Get validation rules for frontend
     */
    public static function getValidationRules(): array
    {
        return [
            'max_duration_days' => [
                'min' => 1,
                'max' => 365,
                'message' => 'Durasi maksimal peminjaman harus antara 1-365 hari'
            ],
            'event_gap_hours' => [
                'min' => 0,
                'max' => 24,
                'message' => 'Jeda antar acara harus antara 0-24 jam'
            ],
            'marking_duration_days' => [
                'min' => 1,
                'max' => 30,
                'message' => 'Masa berlaku marking harus antara 1-30 hari'
            ],
            'max_planned_submit_days' => [
                'min' => 1,
                'max' => 365,
                'message' => 'Batas waktu submit pengajuan harus antara 1-365 hari'
            ],
            'max_active_borrowings' => [
                'min' => 1,
                'max' => 10,
                'message' => 'Batas kuota peminjaman aktif harus antara 1-10'
            ],
        ];
    }
}



