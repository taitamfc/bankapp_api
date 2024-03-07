<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OwnerBank;

class OwnerBankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ownerBanks = [
            [
                'name' => 'VietCombank',
                'status' => 0,
                'image' => 'bank_a.jpg',
                'short_description' => 'Short description for Bank A',
                'description' => 'Description for Bank A',
                'account_number' => 1033016936,
                'bin' => 970436,
                'account_name' => 'HOANG VAN LONG',
                'note' => 'Nạp tiền vào web',
            ],
            [
                'name' => 'Techcombank',
                'status' => 0,
                'image' => 'bank_b.jpg',
                'short_description' => 'Short description for Bank B',
                'description' => 'Description for Bank B',
                'account_number' => '9876543210',
                'bin' => 970407,
                'account_name' => 'Account Name B',
                'note' => 'Nạp tiền vào web',
            ],
        ];

        foreach ($ownerBanks as $bank) {
            OwnerBank::create($bank);
        }
    }
}
