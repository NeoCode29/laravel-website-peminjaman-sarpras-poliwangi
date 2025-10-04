<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePrasaranaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:150'],
            'kategori_id' => ['sometimes', 'required', 'integer', 'exists:kategori_prasarana,id'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'required', 'in:tersedia,rusak,maintenance'],
            'kapasitas' => ['nullable', 'integer', 'min:0'],
            'lokasi' => ['nullable', 'string', 'max:150'],
            'images.*' => ['nullable', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
        ];
    }
}



