<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bank;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bankData = [
            [
                'name' => 'Ngân hàng Ngoại thương Việt Nam (Vietcombank)',
                'status' => 1,
                'image' => 'images/bank/VCB.png',
                'bin' => 970436,
                'type' => "VIETCOMBANK",
                'opening_fee' => 0,
                'account_opening_fee' => 100000,
                'max_accounts' => 10,
                'app_deposit_fee' => 20000,
                'app_deposit_fee_percentage' => 0.1,
                'app_transfer_fee' => 88000,
                'app_transfer_fee_percentage' => 0.1,
                'auto_check_account_fee' => 2000,
                'important_note' => null,
                'android_version' => 'v1.0.0',
                'ios_version' => 'v1.0.0',
                'android_download_link' => 'Đường Link',
                'ios_download_link' => 'Đường link',
            ],
            [
                'name' => 'Ngân hàng Công thương (Techcombank)',
                'status' => 1,
                'image' => 'images/bank/Techcombank-logo.jpg',
                'bin' => 970407,
                'type' => "TECHCOMBANK",
                'opening_fee' => 0,
                'account_opening_fee' => 100000,
                'max_accounts' => 10,
                'app_deposit_fee' => 20000,
                'app_deposit_fee_percentage' => 0.1,
                'app_transfer_fee' => 88000,
                'app_transfer_fee_percentage' => 0.1,
                'auto_check_account_fee' => 2000,
                'important_note' => null,
                'android_version' => 'v1.0.0',
                'ios_version' => 'v1.0.0',
                'android_download_link' => 'Đường Link',
                'ios_download_link' => 'Đường link',
            ],
        ];

        foreach ($bankData as $data) {
            Bank::create([
                'name' => $data['name'],
                'status' => $data['status'],
                'image' => $data['image'],
                'bin' => $data['bin'],
                'type' => $data['type'],
                'opening_fee' => $data['opening_fee'],
                'account_opening_fee' => $data['account_opening_fee'],
                'max_accounts' => $data['max_accounts'],
                'app_deposit_fee' => $data['app_deposit_fee'],
                'app_transfer_fee_percentage' => $data['app_transfer_fee_percentage'],
                'app_deposit_fee_percentage' => $data['app_deposit_fee_percentage'],
                'auto_check_account_fee' => $data['auto_check_account_fee'],
                'important_note' => $data['important_note'],
                'android_version' => $data['android_version'],
                'ios_version' => $data['ios_version'],
                'android_download_link' => $data['android_download_link'],
                'android_download_link' => $data['android_download_link'],
                'ios_download_link' => $data['ios_download_link'],
            ]);
        }
       
    }
}
