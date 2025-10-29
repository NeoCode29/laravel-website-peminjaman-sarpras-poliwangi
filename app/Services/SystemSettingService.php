<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SystemSettingService
{
    const CACHE_PREFIX = 'system_setting_';
    const CACHE_TTL = 3600; // 1 hour

    /**
     * Get all system settings
     */
    public function getAllSettings(): array
    {
        return Cache::remember('all_system_settings', self::CACHE_TTL, function () {
            return SystemSetting::getAllAsArray();
        });
    }

    /**
     * Get setting value by key
     */
    public function getSetting(string $key, $default = null)
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
     * Set setting value by key
     */
    public function setSetting(string $key, $value, string $description = null): array
    {
        try {
            // Validate setting based on key
            $validation = $this->validateSetting($key, $value);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'errors' => $validation['errors']
                ];
            }

            // Update or create setting
            $result = SystemSetting::setValue($key, $value, $description);
            
            if ($result) {
                // Clear cache
                $this->clearSettingCache($key);
                $this->clearAllSettingsCache();
                
                // Log the change
                Log::info("System setting updated", [
                    'key' => $key,
                    'value' => $value,
                    'updated_by' => auth()->id()
                ]);

                return [
                    'success' => true,
                    'message' => 'Setting berhasil diperbarui',
                    'data' => [
                        'key' => $key,
                        'value' => $value,
                        'description' => $description
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal memperbarui setting'
            ];

        } catch (\Exception $e) {
            Log::error("Error updating system setting", [
                'key' => $key,
                'value' => $value,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui setting'
            ];
        }
    }

    /**
     * Update multiple settings
     */
    public function updateMultipleSettings(array $settings): array
    {
        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($settings as $key => $data) {
            $value = $data['value'] ?? null;
            $description = $data['description'] ?? null;

            $result = $this->setSetting($key, $value, $description);
            
            if ($result['success']) {
                $successCount++;
            } else {
                $errorCount++;
            }

            $results[$key] = $result;
        }

        return [
            'success' => $errorCount === 0,
            'message' => "Berhasil memperbarui {$successCount} setting" . ($errorCount > 0 ? ", {$errorCount} gagal" : ""),
            'results' => $results,
            'summary' => [
                'total' => count($settings),
                'success' => $successCount,
                'error' => $errorCount
            ]
        ];
    }

    /**
     * Validate setting value based on key
     */
    public function validateSetting(string $key, $value): array
    {
        $rules = $this->getValidationRules();
        
        if (!isset($rules[$key])) {
            return [
                'valid' => true,
                'message' => null,
                'errors' => []
            ];
        }

        $validator = Validator::make(
            [$key => $value],
            [$key => $rules[$key]['rule']],
            $rules[$key]['messages'] ?? []
        );

        if ($validator->fails()) {
            return [
                'valid' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()->toArray()
            ];
        }

        return [
            'valid' => true,
            'message' => null,
            'errors' => []
        ];
    }

    /**
     * Get validation rules for settings
     */
    private function getValidationRules(): array
    {
        return [
            'max_duration_days' => [
                'rule' => 'required|integer|min:1|max:365',
                'messages' => [
                    'required' => 'Durasi maksimal peminjaman wajib diisi',
                    'integer' => 'Durasi maksimal peminjaman harus berupa angka',
                    'min' => 'Durasi maksimal peminjaman minimal 1 hari',
                    'max' => 'Durasi maksimal peminjaman maksimal 365 hari'
                ]
            ],
            'event_gap_hours' => [
                'rule' => 'required|integer|min:0|max:24',
                'messages' => [
                    'required' => 'Jeda antar acara wajib diisi',
                    'integer' => 'Jeda antar acara harus berupa angka',
                    'min' => 'Jeda antar acara minimal 0 jam',
                    'max' => 'Jeda antar acara maksimal 24 jam'
                ]
            ],
            'marking_duration_days' => [
                'rule' => 'required|integer|min:1|max:30',
                'messages' => [
                    'required' => 'Masa berlaku marking wajib diisi',
                    'integer' => 'Masa berlaku marking harus berupa angka',
                    'min' => 'Masa berlaku marking minimal 1 hari',
                    'max' => 'Masa berlaku marking maksimal 30 hari'
                ]
            ],
            'max_planned_submit_days' => [
                'rule' => 'required|integer|min:1|max:365',
                'messages' => [
                    'required' => 'Batas waktu submit pengajuan wajib diisi',
                    'integer' => 'Batas waktu submit pengajuan harus berupa angka',
                    'min' => 'Batas waktu submit pengajuan minimal 1 hari',
                    'max' => 'Batas waktu submit pengajuan maksimal 365 hari'
                ]
            ],
            'max_active_borrowings' => [
                'rule' => 'required|integer|min:1|max:10',
                'messages' => [
                    'required' => 'Batas kuota peminjaman aktif wajib diisi',
                    'integer' => 'Batas kuota peminjaman aktif harus berupa angka',
                    'min' => 'Batas kuota peminjaman aktif minimal 1',
                    'max' => 'Batas kuota peminjaman aktif maksimal 10'
                ]
            ],
            'notifications_enabled' => [
                'rule' => 'required|boolean',
                'messages' => [
                    'required' => 'Status notifikasi wajib diisi',
                    'boolean' => 'Status notifikasi harus berupa true atau false'
                ]
            ]
        ];
    }

    /**
     * Get setting with description
     */
    public function getSettingWithDescription(string $key): ?array
    {
        $setting = SystemSetting::where('key', $key)->first();
        
        if (!$setting) {
            return null;
        }

        return [
            'key' => $setting->key,
            'value' => $setting->value,
            'description' => $setting->description,
            'created_at' => $setting->created_at,
            'updated_at' => $setting->updated_at
        ];
    }

    /**
     * Get all settings with descriptions
     */
    public function getAllSettingsWithDescriptions(): array
    {
        return Cache::remember('all_system_settings_with_descriptions', self::CACHE_TTL, function () {
            return SystemSetting::select('key', 'value', 'description', 'created_at', 'updated_at')
                ->get()
                ->toArray();
        });
    }

    /**
     * Reset setting to default
     */
    public function resetToDefault(string $key): array
    {
        $defaults = SystemSetting::getDefaultSettings();
        
        if (!isset($defaults[$key])) {
            return [
                'success' => false,
                'message' => 'Setting tidak memiliki nilai default'
            ];
        }

        $result = $this->setSetting(
            $key,
            $defaults[$key]['value'],
            $defaults[$key]['description']
        );

        if ($result['success']) {
            $result['message'] = 'Setting berhasil direset ke nilai default';
        }

        return $result;
    }

    /**
     * Reset all settings to default
     */
    public function resetAllToDefault(): array
    {
        $defaults = SystemSetting::getDefaultSettings();
        $results = [];

        foreach ($defaults as $key => $data) {
            $results[$key] = $this->setSetting($key, $data['value'], $data['description']);
        }

        $successCount = collect($results)->where('success', true)->count();
        $totalCount = count($defaults);

        return [
            'success' => $successCount === $totalCount,
            'message' => "Berhasil mereset {$successCount} dari {$totalCount} setting ke nilai default",
            'results' => $results
        ];
    }

    /**
     * Clear cache for specific setting
     */
    private function clearSettingCache(string $key): void
    {
        Cache::forget(self::CACHE_PREFIX . $key);
    }

    /**
     * Clear all settings cache
     */
    private function clearAllSettingsCache(): void
    {
        Cache::forget('all_system_settings');
        Cache::forget('all_system_settings_with_descriptions');
    }

    /**
     * Clear all cache
     */
    public function clearAllCache(): void
    {
        $this->clearAllSettingsCache();
        
        // Clear individual setting caches
        $keys = SystemSetting::pluck('key');
        foreach ($keys as $key) {
            $this->clearSettingCache($key);
        }
    }

    /**
     * Get system statistics
     */
    public function getSystemStats(): array
    {
        $settings = SystemSetting::getAllAsArray();
        
        return [
            'total_settings' => count($settings),
            'max_duration_days' => (int) ($settings['max_duration_days'] ?? 7),
            'event_gap_hours' => (int) ($settings['event_gap_hours'] ?? 2),
            'marking_duration_days' => (int) ($settings['marking_duration_days'] ?? 3),
            'max_planned_submit_days' => (int) ($settings['max_planned_submit_days'] ?? 30),
            'max_active_borrowings' => (int) ($settings['max_active_borrowings'] ?? 3),
            'notifications_enabled' => filter_var($settings['notifications_enabled'] ?? 'true', FILTER_VALIDATE_BOOLEAN)
        ];
    }
}
