<?php

namespace Database\Factories;

use App\Models\Opd;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opd>
 */
class OpdFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama_opd' => 'Dinas '.fake()->unique()->words(2, true),
            'alamat' => fake()->streetAddress().', Tomohon',
            'kontak' => fake()->numerify('0431-######'),
            'kepala_opd' => fake()->name(),
        ];
    }
}
