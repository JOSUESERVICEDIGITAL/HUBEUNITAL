<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class JosueservicedigitalSeeder extends Seeder
{
    public function run(): void
    {
        $password = config('seeding.superadmin_password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('Définissez SEED_SUPERADMIN_PASSWORD avant de lancer ce seeder.');
        }

        User::updateOrCreate(
            ['email' => 'josueservicedigital@gmail.com'],
            [
                'name' => 'josueservicedigital',
                'email_verified_at' => now(),
                'email_verification_code' => null,
                'password' => Hash::make($password),
                'image' => '20261029131208.png',
                'role' => 'superadmin',
            ],
        );
    }
}
