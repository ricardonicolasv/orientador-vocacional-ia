<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'orientador@test.cl')],
            [
                'name' => env('ADMIN_NAME', 'Orientador'),
                'password' => bcrypt(env('ADMIN_PASSWORD', 'CambiaEstaClave123')),
            ]
        );
    }
}
