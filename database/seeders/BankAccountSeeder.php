<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserBankAccount;

class BankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankData = [
            [
                'name' => 'Ngân hàng Ngoại thương Việt Nam (Vietcombank)',
                'bank_number' => '123456789',
                'bank_username' => 'Nguyễn Văn A',
                'type' => 'VIETCOMBANK',
                'user_id' => 1,
            ],
            [
                'name' => 'Ngân hàng Quân đội (MBBank)',
                'bank_number' => '987654321',
                'bank_username' => 'Trần Thị B',
                'type' => 'MBBANK',
                'user_id' => 1,
            ],
        ];

        foreach ($bankData as $data) {
            UserBankAccount::create([
                'name' => $data['name'],
                'bank_number' => $data['bank_number'],
                'bank_username' => $data['bank_username'],
                'type' => $data['type'],
                'user_id' => $data['user_id'],
            ]);
        }
    }
}
