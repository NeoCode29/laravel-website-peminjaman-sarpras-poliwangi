<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaranaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'kategori_id' => 'required|exists:kategori_sarana,id',
            'type' => 'required|in:serialized,pooled',
            'jumlah_total' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'lokasi' => 'nullable|string|max:150',
            'jumlah_tersedia' => 'nullable|integer|min:0',
            'jumlah_rusak' => 'nullable|integer|min:0',
            'jumlah_maintenance' => 'nullable|integer|min:0',
            'jumlah_hilang' => 'nullable|integer|min:0',
        ];
    }
}



