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
