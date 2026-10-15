<?php

namespace App\Models;

use Database\Factories\OpdFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    /** @use HasFactory<OpdFactory> */
    use HasFactory;

    protected $table = 'opd';

    protected $fillable = [
        'nama_opd',
        'alamat',
        'kontak',
        'kepala_opd',
    ];

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }
}
