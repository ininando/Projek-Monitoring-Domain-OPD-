<?php

namespace Database\Factories;

use App\Models\Domain;
use App\Models\Opd;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Domain>
 */
class DomainFactory extends Factory
{
    public function definition(): array
    {
        return [
            'opd_id' => Opd::factory(),
            'nama_domain' => fake()->unique()->domainWord().'.tomohon.go.id',
            'tanggal_registrasi' => fake()->dateTimeBetween('-5 years', '-1 month'),
            'tanggal_expired' => fake()->dateTimeBetween('-30 days', '+1 year'),
            'status' => fake()->randomElement(['aktif', 'aktif', 'aktif', 'tidak_aktif']),
            'registrar' => fake()->randomElement(['PANDI', 'Rumahweb', 'Niagahoster', 'IDCloudHost']),
            'nameserver' => 'ns1.tomohon.go.id, ns2.tomohon.go.id',
            'ip_address' => fake()->ipv4(),
            'hosting_provider' => fake()->randomElement(['Server Diskominfo', 'Niagahoster', 'IDCloudHost', 'Biznet Gio']),
            'ssl_status' => fake()->randomElement(['aktif', 'tidak_aktif', null]),
            'keterangan' => null,
        ];
    }

    /**
     * Atur tanggal expired relatif terhadap hari ini (negatif = sudah lewat).
     */
    public function expiredDalam(int $hari): static
    {
        return $this->state(fn () => ['tanggal_expired' => today()->addDays($hari)]);
    }
}
