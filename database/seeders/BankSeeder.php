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
        Bank::create([
                'name' => 'VietComBank',
                'status' => 1,
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
                'name' => 'MB bank',
                'status' => 1,
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
        );
    }
}
