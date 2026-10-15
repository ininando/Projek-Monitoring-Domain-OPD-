<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpdRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hak akses ditegakkan oleh middleware pada route group.
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_opd' => ['required', 'string', 'max:200'],
            'alamat' => ['nullable', 'string'],
            'kontak' => ['nullable', 'string', 'max:50'],
            'kepala_opd' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_opd' => 'nama OPD',
            'alamat' => 'alamat',
            'kontak' => 'kontak',
            'kepala_opd' => 'kepala OPD',
        ];
    }
}
