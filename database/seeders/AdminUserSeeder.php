<?php

namespace Database\Seeders;

use App\Models\AuthUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AuthUser::updateOrCreate(
            ['username' => 'admin'],
            [
                'password' => Hash::make('password'),
            ]
        );
    }
}
