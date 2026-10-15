<?php

namespace App\Http\Requests;

use App\Models\Domain;
use App\Services\LaporanDomain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi parameter laporan (dipakai untuk pratinjau dan unduhan PDF/Excel).
 */
class LaporanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', Rule::in(array_keys(LaporanDomain::JENIS))],

            // Per OPD: OPD wajib, periode (rentang tanggal expired) opsional.
            'opd_id' => ['exclude_unless:jenis,per_opd', 'required', 'integer', Rule::exists('opd', 'id')],
            'periode_dari' => ['exclude_unless:jenis,per_opd', 'nullable', 'date_format:Y-m-d'],
            'periode_sampai' => ['exclude_unless:jenis,per_opd', 'nullable', 'date_format:Y-m-d', 'after_or_equal:periode_dari'],

            // Per status.
            'status' => ['exclude_unless:jenis,per_status', 'required', Rule::in(array_keys(Domain::STATUS))],

            // Domain expired: sudah, akan (dalam N hari), atau keduanya.
            'kategori' => ['exclude_unless:jenis,expired', 'required', Rule::in(array_keys(LaporanDomain::KATEGORI_EXPIRED))],
            'dalam_hari' => ['exclude_unless:jenis,expired', 'required', 'integer', Rule::in(LaporanDomain::PILIHAN_HARI)],

            'format' => ['sometimes', Rule::in(['pdf', 'excel'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'jenis' => 'jenis laporan',
            'opd_id' => 'OPD',
            'periode_dari' => 'tanggal awal periode',
            'periode_sampai' => 'tanggal akhir periode',
            'status' => 'status',
            'kategori' => 'kategori expired',
            'dalam_hari' => 'rentang hari',
            'format' => 'format',
        ];
    }

    public function messages(): array
    {
        return [
            'periode_sampai.after_or_equal' => 'Tanggal akhir periode harus sama dengan atau setelah tanggal awal.',
        ];
    }
}
