<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SystemSettingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->can('system.settings');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $action = $this->route()->getActionMethod();
        
        switch ($action) {
            case 'store':
            case 'update':
                return $this->getUpdateRules();
            case 'updateMultiple':
                return $this->getMultipleUpdateRules();
            default:
                return [];
        }
    }

    /**
     * Get validation rules for single setting update
     */
    private function getUpdateRules(): array
    {
        $key = $this->input('key');
        
        $rules = [
            'key' => 'required|string|max:150',
            'value' => 'required',
            'description' => 'nullable|string|max:255'
        ];

        // Add specific validation based on key
        if ($key) {
            $specificRules = $this->getSpecificValidationRules($key);
            if ($specificRules) {
                $rules['value'] = $specificRules;
            }
        }

        return $rules;
    }

    /**
     * Get validation rules for multiple settings update
     */
    private function getMultipleUpdateRules(): array
    {
        return [
            'settings' => 'required|array|min:1',
            'settings.*.key' => 'required|string|max:150',
            'settings.*.value' => 'required',
            'settings.*.description' => 'nullable|string|max:255'
        ];
    }

    /**
     * Get specific validation rules based on setting key
     */
    private function getSpecificValidationRules(string $key): ?string
    {
        $rules = [
            'max_duration_days' => 'required|integer|min:1|max:365',
            'event_gap_hours' => 'required|integer|min:0|max:24',
            'marking_duration_days' => 'required|integer|min:1|max:30',
            'max_planned_submit_days' => 'required|integer|min:1|max:365',
            'max_active_borrowings' => 'required|integer|min:1|max:10',
            'notifications_enabled' => 'required|boolean'
        ];

        return $rules[$key] ?? null;
    }

    /**
     * Get custom messages for validation errors
     */
    public function messages(): array
    {
        return [
            'key.required' => 'Kunci setting wajib diisi',
            'key.string' => 'Kunci setting harus berupa string',
            'key.max' => 'Kunci setting maksimal 150 karakter',
            'value.required' => 'Nilai setting wajib diisi',
            'description.string' => 'Deskripsi harus berupa string',
            'description.max' => 'Deskripsi maksimal 255 karakter',
            'settings.required' => 'Data settings wajib diisi',
            'settings.array' => 'Data settings harus berupa array',
            'settings.min' => 'Minimal 1 setting yang harus diupdate',
            'settings.*.key.required' => 'Kunci setting wajib diisi',
            'settings.*.key.string' => 'Kunci setting harus berupa string',
            'settings.*.key.max' => 'Kunci setting maksimal 150 karakter',
            'settings.*.value.required' => 'Nilai setting wajib diisi',
            'settings.*.description.string' => 'Deskripsi harus berupa string',
            'settings.*.description.max' => 'Deskripsi maksimal 255 karakter',
            
            // Specific validation messages
            'value.integer' => 'Nilai harus berupa angka',
            'value.min' => 'Nilai terlalu kecil',
            'value.max' => 'Nilai terlalu besar',
            'value.boolean' => 'Nilai harus berupa true atau false',
            
            // Key-specific messages
            'value.required' => 'Nilai setting wajib diisi',
        ];
    }

    /**
     * Get custom attributes for validation errors
     */
    public function attributes(): array
    {
        return [
            'key' => 'kunci setting',
            'value' => 'nilai setting',
            'description' => 'deskripsi',
            'settings' => 'data settings',
            'settings.*.key' => 'kunci setting',
            'settings.*.value' => 'nilai setting',
            'settings.*.description' => 'deskripsi'
        ];
    }

    /**
     * Configure the validator instance
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation logic can be added here
            $this->validateKeyExists($validator);
        });
    }

    /**
     * Validate that the key exists in allowed keys
     */
    private function validateKeyExists($validator)
    {
        $allowedKeys = [
            'max_duration_days',
            'event_gap_hours', 
            'marking_duration_days',
            'max_planned_submit_days',
            'max_active_borrowings',
            'notifications_enabled'
        ];

        $key = $this->input('key');
        
        if ($key && !in_array($key, $allowedKeys)) {
            $validator->errors()->add('key', 'Kunci setting tidak valid');
        }
    }
}



