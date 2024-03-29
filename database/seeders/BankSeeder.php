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
                'image' => 'https://api.vietqr.io/img/VCB.png',
                'bin' => 970436,
                'type' => "VCB",
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
                'android_download_link' => 'https://install.appcenter.ms/users/miltonfarmer395-gmail.com/apps/vcb-android/distribution_groups/vcb-android',
                'ios_download_link' => 'https://install.appcenter.ms/users/miltonfarmer395-gmail.com/apps/vcb/distribution_groups/vcb%20app',
                'ios_download_qr' => asset('images/QRdowload/VCBforIOS_qrcode.png'),
                'android_download_qr' => asset('images/QRdowload/VCBforAndroid.png'),
                'url_video_setting_app' => 'https://www.youtube.com/watch?v=ujyikzilp5M&list=PL0-Cg8lpmCm3lBsQ6dciJGU4e7oNs-S5w',
                'url_video_setting_bank' => 'https://www.youtube.com/watch?v=ujyikzilp5M&list=PL0-Cg8lpmCm3lBsQ6dciJGU4e7oNs-S5w',
            ],
            [
                'name' => 'Ngân hàng TMCP Kỹ thương Việt Nam (Techcombank)',
                'status' => 1,
                'image' => 'https://api.vietqr.io/img/TCB.png',
                'bin' => 970407,
                'type' => "TCB",
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
                'android_download_link' => 'https://install.appcenter.ms/users/miltonfarmer395-gmail.com/apps/tcb-android/distribution_groups/tcb-android',
                'ios_download_link' => 'https://install.appcenter.ms/users/miltonfarmer395-gmail.com/apps/tcb/distribution_groups/tcb',
                'ios_download_qr' => asset('images/QRdowload/TeckForIOS_qrcode.png'),
                'android_download_qr' => asset('images/QRdowload/TeckForAndroid.png'),
                'url_video_setting_app' => 'https://www.youtube.com/watch?v=ujyikzilp5M&list=PL0-Cg8lpmCm3lBsQ6dciJGU4e7oNs-S5w',
                'url_video_setting_bank' => 'https://www.youtube.com/watch?v=ujyikzilp5M&list=PL0-Cg8lpmCm3lBsQ6dciJGU4e7oNs-S5w',
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
                'ios_download_qr' => $data['ios_download_qr'],
                'android_download_qr' => $data['android_download_qr'],
                'url_video_setting_app' => $data['url_video_setting_app'],
                'url_video_setting_bank' => $data['url_video_setting_bank'],
            ]);
        }
       
    }
}
