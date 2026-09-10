<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower(trim((string) env('ADMIN_EMAIL', 'seeds@gmail.com')));
        $password = (string) env('ADMIN_PASSWORD', '');
        $isProduction = app()->environment('production');

        if ($isProduction && ($password === '' || $password === 'seeds123')) {
            throw new RuntimeException('Set ADMIN_EMAIL and a strong ADMIN_PASSWORD before seeding production.');
        }

        if ($password === '') {
            $password = 'seeds123';
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => 'Seeds Admin',
            'password' => $password,
            'is_admin' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();
    }
}
