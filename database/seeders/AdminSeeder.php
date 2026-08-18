<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'seeds@gmail.com'],
            [
                'name' => 'Seeds Admin',
                'password' => 'seeds123',
                'is_admin' => true,
            ]
        );
    }
}
