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
                'account_number' => '0123456789',
                'account_name' => 'Account Name A',
                'note' => 'Note for Bank A',
            ],
            [
                'name' => 'Bank MB',
                'status' => 0,
                'image' => 'bank_b.jpg',
                'short_description' => 'Short description for Bank B',
                'description' => 'Description for Bank B',
                'account_number' => '9876543210',
                'account_name' => 'Account Name B',
                'note' => 'Note for Bank B',
            ],
        ];

        foreach ($ownerBanks as $bank) {
            OwnerBank::create($bank);
        }
    }
}
