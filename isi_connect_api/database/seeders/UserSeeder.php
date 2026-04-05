<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Profile;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin principal
        $admin = User::updateOrCreate(
            ['email' => 'multibrainmusic1@gmail.com'],
            [
                'name' => 'Admin Alumni',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'promotion_year' => 2024
            ]
        );
        Profile::firstOrCreate(['user_id' => $admin->id]);

        // Compte de test ISI
        $user = User::updateOrCreate(
            ['email' => 'bniasseisidp@groupeisi.com'],
            [
                'name' => 'User ISI',
                'password' => Hash::make('password'),
                'promotion_year' => 2024
            ]
        );
        Profile::firstOrCreate(['user_id' => $user->id]);
    }
}
