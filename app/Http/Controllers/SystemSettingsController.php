<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemSettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', SystemSetting::class);

        $settings = SystemSetting::orderBy('key')->get();
        $editableSettings = $settings->where('is_editable', true);
        $nonEditableSettings = $settings->where('is_editable', false);

        return view('system-settings.index', compact('editableSettings', 'nonEditableSettings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SystemSetting $systemSetting)
    {
        $this->authorize('update', $systemSetting);

        if (!$systemSetting->is_editable) {
            return redirect()->route('system-settings.index')
                ->with('error', 'Setting ini tidak dapat diedit.');
        }

        return view('system-settings.edit', compact('systemSetting'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SystemSetting $systemSetting)
    {
        $this->authorize('update', $systemSetting);

        if (!$systemSetting->is_editable) {
            return redirect()->route('system-settings.index')
                ->with('error', 'Setting ini tidak dapat diedit.');
        }

        $validator = Validator::make($request->all(), [
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Validate value based on type
        $validationRules = $this->getValidationRules($systemSetting->type);
        $validator = Validator::make($request->all(), [
            'value' => $validationRules,
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $systemSetting->update([
                'value' => $request->value,
            ]);

            return redirect()->route('system-settings.index')
                ->with('success', 'Setting berhasil diperbarui.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui setting.')
                ->withInput();
        }
    }

    /**
     * Bulk update multiple settings.
     */
    public function bulkUpdate(Request $request)
    {
        $this->authorize('update', SystemSetting::class);

        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.id' => 'required|exists:system_settings,id',
            'settings.*.value' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $updatedCount = 0;
        $errors = [];

        foreach ($request->settings as $settingData) {
            try {
                $setting = SystemSetting::find($settingData['id']);
                
                if (!$setting->is_editable) {
                    $errors[] = "Setting '{$setting->key}' tidak dapat diedit.";
                    continue;
                }

                // Validate value based on type
                $validationRules = $this->getValidationRules($setting->type);
                $validator = Validator::make(['value' => $settingData['value']], [
                    'value' => $validationRules,
                ]);

                if ($validator->fails()) {
                    $errors[] = "Setting '{$setting->key}': " . implode(', ', $validator->errors()->all());
                    continue;
                }

                $setting->update(['value' => $settingData['value']]);
                $updatedCount++;

            } catch (\Exception $e) {
                $errors[] = "Setting '{$setting->key}': " . $e->getMessage();
            }
        }

        $message = "Berhasil memperbarui {$updatedCount} setting.";
        if (!empty($errors)) {
            $message .= " Error: " . implode('; ', $errors);
        }

        return redirect()->route('system-settings.index')
            ->with($errors ? 'warning' : 'success', $message);
    }

    /**
     * Reset settings to default values.
     */
    public function reset()
    {
        $this->authorize('update', SystemSetting::class);

        try {
            // Reset to default values
            $defaults = [
                'max_duration_days' => '7',
                'event_gap_hours' => '2',
                'marking_duration_days' => '3',
                'max_planned_submit_days' => '30',
                'max_active_borrowings' => '3',
                'notifications_enabled' => 'true',
                'session_timeout_hours' => '8',
                'file_upload_max_size_mb' => '5',
                'audit_log_retention_days' => '365',
            ];

            foreach ($defaults as $key => $value) {
                $setting = SystemSetting::where('key', $key)->first();
                if ($setting && $setting->is_editable) {
                    $setting->update(['value' => $value]);
                }
            }

            return redirect()->route('system-settings.index')
                ->with('success', 'Settings berhasil direset ke nilai default.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat reset settings.');
        }
    }

    /**
     * Get validation rules based on setting type.
     */
    private function getValidationRules($type)
    {
        switch ($type) {
            case 'integer':
                return 'required|integer|min:0';
            case 'boolean':
                return 'required|boolean';
            case 'json':
                return 'required|json';
            case 'string':
            default:
                return 'required|string|max:1000';
        }
    }

    /**
     * Get setting value by key (API endpoint).
     */
    public function getValue($key)
    {
        $this->authorize('viewAny', SystemSetting::class);

        $value = SystemSetting::getValue($key);
        
        return response()->json([
            'key' => $key,
            'value' => $value,
        ]);
    }

    /**
     * Set setting value by key (API endpoint).
     */
    public function setValue(Request $request, $key)
    {
        $this->authorize('update', SystemSetting::class);

        $setting = SystemSetting::where('key', $key)->first();
        
        if (!$setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        if (!$setting->is_editable) {
            return response()->json(['error' => 'Setting is not editable'], 403);
        }

        $validator = Validator::make($request->all(), [
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        // Validate value based on type
        $validationRules = $this->getValidationRules($setting->type);
        $validator = Validator::make($request->all(), [
            'value' => $validationRules,
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $setting->update(['value' => $request->value]);
            
            return response()->json([
                'key' => $key,
                'value' => $setting->getCastedValue(),
                'message' => 'Setting updated successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update setting'], 500);
        }
    }

    /**
     * Get all settings (API endpoint).
     */
    public function getAll()
    {
        $this->authorize('viewAny', SystemSetting::class);

        $settings = SystemSetting::getAllSettings();
        
        return response()->json($settings);
    }

    /**
     * Clear settings cache.
     */
    public function clearCache()
    {
        $this->authorize('update', SystemSetting::class);

        try {
            SystemSetting::clearCache();
            
            return redirect()->route('system-settings.index')
                ->with('success', 'Cache settings berhasil dihapus.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus cache.');
        }
    }
}
