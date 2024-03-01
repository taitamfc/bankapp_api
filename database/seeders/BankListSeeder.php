<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BankList;
class BankListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bankNames = [
            'Ngân hàng Ngoại thương Việt Nam (Vietcombank)',
            'Ngân hàng Công thương Việt Nam (VietinBank)',
            'Ngân hàng Quân đội (MB Bank)',
            'Ngân hàng Công thương (Techcombank)',
            'Ngân hàng Đầu tư và Phát triển Việt Nam (BIDV)',
            'Ngân hàng Sài Gòn Thương Tín (Sacombank)',
            'Ngân hàng Á Châu (ACB)',
            'Ngân hàng Tiên Phong (TPBank)',
            'Ngân hàng Đông Á (DongA Bank)',
            'Ngân hàng Eximbank',
        ];

        foreach ($bankNames as $bankName) {
            BankList::create([
                'name' => $bankName,
            ]);
        }
    }
}
