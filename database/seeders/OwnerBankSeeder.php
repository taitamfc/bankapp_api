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
                'name' => 'Ngân hàng TMCP Phương Đông',
                'status' => 0,
                'image' => 'https://api.vietqr.io/img/OCB.png',
                'short_description' => 'Short description for Bank A',
                'description' => 'Description for Bank A',
                'account_number' => 10486734033,
                'bin' => 970448,
                'account_name' => 'HOANG VAN B',
                'note' => 'Nạp tiền vào web',
            ],
            [
                'name' => 'Ngân hàng Thương Mại Cổ Phần Á Châu',
                'status' => 0,
                'image' => 'https://api.vietqr.io/img/ACB.png',
                'short_description' => 'Short description for Bank ACB',
                'description' => 'Description for Bank ACB',
                'account_number' => 40910727,
                'bin' => 970416,
                'account_name' => 'NGUYEN TIEN TRIEU',
                'note' => 'Nạp tiền vào web',
            ],
            // [
            //     'name' => 'Ngân hàng Công thương (Techcombank)',
            //     'status' => 0,
            //     'image' => 'http://127.0.0.1:8000/images/bank/Techcombank-logo.jpg',
            //     'short_description' => 'Short description for Bank B',
            //     'description' => 'Description for Bank B',
            //     'account_number' => '9876543210',
            //     'bin' => 970407,
            //     'account_name' => 'Account Name B',
            //     'note' => 'Nạp tiền vào web',
            // ],
        ];

        foreach ($ownerBanks as $bank) {
            OwnerBank::create($bank);
        }
    }
}
