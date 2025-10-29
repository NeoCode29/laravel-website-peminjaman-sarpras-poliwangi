<?php

namespace App\Http\Controllers;

use App\Http\Requests\SystemSettingRequest;
use App\Services\SystemSettingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SystemSettingController extends Controller
{
    protected $systemSettingService;

    public function __construct(SystemSettingService $systemSettingService)
    {
        $this->middleware('auth');
        $this->middleware('permission:system.settings');
        $this->systemSettingService = $systemSettingService;
    }

    /**
     * Display a listing of system settings
     */
    public function index()
    {
        try {
            $settings = $this->systemSettingService->getAllSettings();
            $stats = $this->systemSettingService->getSystemStats();
            
            return view('system-settings.index', compact('settings', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error fetching system settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengambil data settings');
        }
    }

    /**
     * Get system settings data for API
     */
    public function getSettingsData(): JsonResponse
    {
        try {
            $settings = $this->systemSettingService->getAllSettingsWithDescriptions();
            
            return response()->json([
                'success' => true,
                'message' => 'Data settings berhasil diambil',
                'data' => $settings
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching system settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data settings'
            ], 500);
        }
    }

    /**
     * Get specific setting by key
     */
    public function show(string $key): JsonResponse
    {
        try {
            $setting = $this->systemSettingService->getSettingWithDescription($key);
            
            if (!$setting) {
                return response()->json([
                    'success' => false,
                    'message' => 'Setting tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data setting berhasil diambil',
                'data' => $setting
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching system setting', [
                'key' => $key,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data setting'
            ], 500);
        }
    }

    /**
     * Store a new system setting
     */
    public function store(SystemSettingRequest $request): JsonResponse
    {
        try {
            $result = $this->systemSettingService->setSetting(
                $request->input('key'),
                $request->input('value'),
                $request->input('description')
            );

            if ($result['success']) {
                return response()->json($result, 201);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            Log::error('Error storing system setting', [
                'key' => $request->input('key'),
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan setting'
            ], 500);
        }
    }

    /**
     * Update a specific system setting
     */
    public function update(SystemSettingRequest $request, string $key): JsonResponse
    {
        try {
            $result = $this->systemSettingService->setSetting(
                $key,
                $request->input('value'),
                $request->input('description')
            );

            if ($result['success']) {
                return response()->json($result);
            }

            return response()->json($result, 400);

        } catch (\Exception $e) {
            Log::error('Error updating system setting', [
                'key' => $key,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui setting'
            ], 500);
        }
    }

    /**
     * Update multiple system settings
     */
    public function updateMultiple(SystemSettingRequest $request): JsonResponse
    {
        try {
            $settings = $request->input('settings', []);
            $result = $this->systemSettingService->updateMultipleSettings($settings);

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Error updating multiple system settings', [
                'settings_count' => count($request->input('settings', [])),
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui settings'
            ], 500);
        }
    }

    /**
     * Reset setting to default value
     */
    public function reset(string $key): JsonResponse
    {
        try {
            $result = $this->systemSettingService->resetToDefault($key);

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Error resetting system setting', [
                'key' => $key,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mereset setting'
            ], 500);
        }
    }

    /**
     * Reset all settings to default values
     */
    public function resetAll(): JsonResponse
    {
        try {
            $result = $this->systemSettingService->resetAllToDefault();

            return response()->json($result, $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Error resetting all system settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mereset semua settings'
            ], 500);
        }
    }

    /**
     * Get system statistics
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->systemSettingService->getSystemStats();

            return response()->json([
                'success' => true,
                'message' => 'Data statistik sistem berhasil diambil',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching system stats', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik sistem'
            ], 500);
        }
    }

    /**
     * Clear all settings cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->systemSettingService->clearAllCache();

            return response()->json([
                'success' => true,
                'message' => 'Cache settings berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing system settings cache', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus cache'
            ], 500);
        }
    }

    /**
     * Get setting value by key (for public access)
     */
    public function getValue(string $key): JsonResponse
    {
        try {
            $value = $this->systemSettingService->getSetting($key);

            return response()->json([
                'success' => true,
                'data' => [
                    'key' => $key,
                    'value' => $value
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting system setting value', [
                'key' => $key,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil nilai setting'
            ], 500);
        }
    }

    /**
     * Get all settings as key-value pairs (for public access)
     */
    public function getAllValues(): JsonResponse
    {
        try {
            $settings = $this->systemSettingService->getAllSettings();

            return response()->json([
                'success' => true,
                'data' => $settings
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting all system setting values', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil semua nilai settings'
            ], 500);
        }
    }

    /**
     * Validate setting value without saving
     */
    public function validateSetting(Request $request): JsonResponse
    {
        try {
            $key = $request->input('key');
            $value = $request->input('value');

            if (!$key || !$value) {
                return response()->json([
                    'success' => false,
                    'message' => 'Key dan value wajib diisi'
                ], 400);
            }

            // Use the service validation
            $validation = $this->systemSettingService->validateSetting($key, $value);

            return response()->json([
                'success' => $validation['valid'],
                'message' => $validation['message'],
                'errors' => $validation['errors'] ?? []
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating system setting', [
                'key' => $request->input('key'),
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat validasi setting'
            ], 500);
        }
    }
}
