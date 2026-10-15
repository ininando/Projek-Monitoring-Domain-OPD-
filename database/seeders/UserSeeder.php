<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun default. GANTI password setelah instalasi pertama.
     * Password otomatis di-hash oleh cast 'hashed' pada model User.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@tomohon.go.id'],
            ['name' => 'Super Admin', 'password' => 'superadmin123', 'role' => User::ROLE_SUPER_ADMIN],
        );

        User::updateOrCreate(
            ['email' => 'admin@tomohon.go.id'],
            ['name' => 'Admin Diskominfo', 'password' => 'admin123', 'role' => User::ROLE_ADMIN],
        );
    }
}
