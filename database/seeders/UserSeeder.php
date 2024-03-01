<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userData = [
            [
                'name' => 'admin',
                'email' => 'admin@gmail.com',
                'user_name' => 'adminfc',
                'phone' => '0123456788',
                'password' => Hash::make('123456'),
                'password_confirmation' => Hash::make('123456'),
            ],
            [
                'name' => 'hoàng long',
                'email' => 'hoangvanlong.vn1999@gmail.com',
                'user_name' => 'longfc',
                'phone' => '0123456767',
                'password' => Hash::make('123456'),
                'password_confirmation' => Hash::make('123456'),
            ],
        ];

        foreach ($userData as $data) {
            User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'user_name' => $data['user_name'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'password_confirmation' => $data['password_confirmation'],
            ]);
        }
    }
}
