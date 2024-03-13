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
                'role' => 0,
                'image' => 'http://127.0.0.1:8000/images/profile/avt.jpeg',
                'user_name' => 'adminfc',
                'phone' => '0123456788',
                'password' => Hash::make('123456'),
            ],
            [
                'name' => 'hoàng long',
                'email' => 'hoangvanlong.vn1999@gmail.com',
                'role' => 0,
                'image' => 'http://127.0.0.1:8000/images/profile/avt2.jpg',
                'user_name' => 'longfc',
                'phone' => '0123456767',
                'password' => Hash::make('123456'),
            ],
        ];

        foreach ($userData as $data) {
            User::create([
                'name' => $data['name'],
                'image' => $data['image'],
                'role' => $data['role'],
                'email' => $data['email'],
                'user_name' => $data['user_name'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'last_login' => date('Y-m-d',strtotime('last month')),
                'account_balance' => 100000000,
                'status' => 1
            ]);
        }
    }
}
