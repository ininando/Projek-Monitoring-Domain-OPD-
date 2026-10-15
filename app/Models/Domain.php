<?php

namespace App\Models;

use Database\Factories\DomainFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Domain extends Model
{
    /** @use HasFactory<DomainFactory> */
    use HasFactory;

    public const STATUS = [
        'aktif' => 'Aktif',
        'tidak_aktif' => 'Tidak Aktif',
    ];

    public const SSL_STATUS = [
        'aktif' => 'Aktif',
        'tidak_aktif' => 'Tidak Aktif',
    ];

    /** Batas hari untuk label expired. */
    public const BATAS_CRITICAL = 7;

    public const BATAS_WARNING = 30;

    protected $fillable = [
        'opd_id',
        'nama_domain',
        'tanggal_registrasi',
        'tanggal_expired',
        'status',
        'registrar',
        'nameserver',
        'ip_address',
        'hosting_provider',
        'ssl_status',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_registrasi' => 'date',
            'tanggal_expired' => 'date',
        ];
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessor status expired (satu-satunya sumber logika, jangan diduplikasi di view)
    |--------------------------------------------------------------------------
    */

    /**
     * Sisa hari menuju tanggal expired; negatif jika sudah lewat.
     */
    public function getSisaHariAttribute(): int
    {
        return (int) today()->diffInDays($this->tanggal_expired->copy()->startOfDay(), false);
    }

    public function getSudahExpiredAttribute(): bool
    {
        return $this->sisa_hari < 0;
    }

    /**
     * Critical: <= 7 hari atau sudah lewat, Warning: 8-30 hari, Normal: > 30 hari.
     */
    public function getLabelExpiredAttribute(): string
    {
        return match (true) {
            $this->sisa_hari <= self::BATAS_CRITICAL => 'Critical',
            $this->sisa_hari <= self::BATAS_WARNING => 'Warning',
            default => 'Normal',
        };
    }

    /**
     * Warna Bootstrap untuk badge label expired.
     */
    public function getWarnaLabelAttribute(): string
    {
        return match ($this->label_expired) {
            'Critical' => 'danger',
            'Warning' => 'warning',
            default => 'success',
        };
    }

    /**
     * Teks countdown, misalnya "12 hari lagi" atau "Expired 3 hari lalu".
     */
    public function getTeksSisaHariAttribute(): string
    {
        $sisa = $this->sisa_hari;

        return match (true) {
            $sisa < 0 => 'Expired '.abs($sisa).' hari lalu',
            $sisa === 0 => 'Expired hari ini',
            default => $sisa.' hari lagi',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS[$this->status] ?? $this->status;
    }

    /*
    |--------------------------------------------------------------------------
    | Query scope
    |--------------------------------------------------------------------------
    */

    /**
     * Domain yang belum lewat dan akan expired dalam $days hari ke depan.
     */
    public function scopeAkanExpired(Builder $query, int $days = self::BATAS_WARNING): Builder
    {
        return $query->whereDate('tanggal_expired', '>=', today())
            ->whereDate('tanggal_expired', '<=', today()->addDays($days));
    }

    /**
     * Domain yang tanggal expired-nya sudah lewat.
     */
    public function scopeSudahExpired(Builder $query): Builder
    {
        return $query->whereDate('tanggal_expired', '<', today());
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Terapkan filter list/laporan. Kunci yang dikenali:
     * search, opd_id, status, expired_dari (Y-m-d), expired_sampai (Y-m-d).
     * Semua nilai di-bind sebagai parameter (aman dari SQL injection).
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('nama_domain', 'like', "%{$search}%")
                        ->orWhere('registrar', 'like', "%{$search}%")
                        ->orWhere('hosting_provider', 'like', "%{$search}%");
                });
            })
            ->when($filters['opd_id'] ?? null, fn (Builder $q, $opdId) => $q->where('opd_id', $opdId))
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['expired_dari'] ?? null, fn (Builder $q, $tgl) => $q->whereDate('tanggal_expired', '>=', $tgl))
            ->when($filters['expired_sampai'] ?? null, fn (Builder $q, $tgl) => $q->whereDate('tanggal_expired', '<=', $tgl));
    }
}
