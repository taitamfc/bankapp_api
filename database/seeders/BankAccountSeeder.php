<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\UserBankAccountModel;

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
            ],
            [
                'name' => 'Ngân hàng Công thương Việt Nam (VietinBank)',
                'bank_number' => '987654321',
                'bank_username' => 'Trần Thị B',
            ],
        ];

        foreach ($bankData as $data) {
            UserBankAccountModel::create([
                'name' => $data['name'],
                'bank_number' => $data['bank_number'],
                'bank_username' => $data['bank_username'],
            ]);
        }
    }
}
