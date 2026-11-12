<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Admin dan beberapa penulis (anggota) untuk pengujian.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ],
        );

        foreach ([
            ['penulis1', 'Penulis Satu', 'penulis1@example.com'],
            ['penulis2', 'Penulis Dua', 'penulis2@example.com'],
            ['penulis3', 'Penulis Tiga', 'penulis3@example.com'],
        ] as [$username, $name, $email]) {
            User::query()->updateOrCreate(
                ['username' => $username],
                [
                    'name' => $name,
                    'email' => $email,
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_MEMBER,
                    'email_verified_at' => now(),
                ],
            );
        }
    }
}
