<?php

namespace App\Http\Requests;

use App\Models\Domain;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Hak akses ditegakkan oleh middleware pada route group.
        return true;
    }

    /**
     * Normalisasi nama domain: huruf kecil, tanpa skema http(s):// dan garis miring di akhir.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('nama_domain')) {
            $nama = strtolower(trim((string) $this->input('nama_domain')));
            $nama = preg_replace('#^https?://#', '', $nama);

            $this->merge(['nama_domain' => rtrim($nama, '/')]);
        }
    }

    public function rules(): array
    {
        $domain = $this->route('domain');

        return [
            'nama_domain' => [
                'required', 'string', 'max:255',
                // Contoh valid: diskominfo.tomohon.go.id
                'regex:/^(?=.{1,255}$)([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/',
                Rule::unique('domains', 'nama_domain')->ignore($domain),
            ],
            // Urutan aturan = urutan field di form, agar ringkasan error berurutan sama.
            'opd_id' => ['required', 'integer', Rule::exists('opd', 'id')],
            'tanggal_registrasi' => ['nullable', 'date_format:Y-m-d'],
            'tanggal_expired' => array_filter([
                'required', 'date_format:Y-m-d',
                // Hanya dibandingkan jika tanggal registrasi diisi.
                $this->filled('tanggal_registrasi') ? 'after_or_equal:tanggal_registrasi' : null,
            ]),
            'status' => ['required', Rule::in(array_keys(Domain::STATUS))],
            'registrar' => ['nullable', 'string', 'max:100'],
            'nameserver' => ['nullable', 'string', 'max:1000'],
            'ip_address' => ['nullable', 'ip'],
            'hosting_provider' => ['nullable', 'string', 'max:100'],
            'ssl_status' => ['nullable', Rule::in(array_keys(Domain::SSL_STATUS))],
            'keterangan' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'opd_id' => 'OPD',
            'nama_domain' => 'nama domain',
            'tanggal_registrasi' => 'tanggal registrasi',
            'tanggal_expired' => 'tanggal expired',
            'status' => 'status',
            'registrar' => 'registrar',
            'nameserver' => 'nameserver',
            'ip_address' => 'IP address',
            'hosting_provider' => 'hosting provider',
            'ssl_status' => 'status SSL',
            'keterangan' => 'keterangan',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_domain.regex' => 'Format nama domain tidak valid. Contoh: diskominfo.tomohon.go.id',
            'tanggal_expired.after_or_equal' => 'Tanggal expired harus sama dengan atau setelah tanggal registrasi.',
        ];
    }
}
