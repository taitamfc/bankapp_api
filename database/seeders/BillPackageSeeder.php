<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BillPackage;

class BillPackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $package = [
            [
                'name' => 'Gói bill vip 1',
                'price' => 1000000,
                'type' => 'vip1',
                'max_download_bill' => 100,
                'max_device_login' => 1,
            ],
            [
                'name' => 'Gói bill vip 2',
                'price' => 5000000,
                'type' => 'vip2',
                'max_download_bill' => 300,
                'max_device_login' => 3,
            ],
            [
                'name' => 'Gói bill vip 3',
                'price' => 9000000,
                'type' => 'vip3',
                'max_download_bill' => -1,
                'max_device_login' => 6,
            ],
        ];

        foreach ($package as $item) {
            BillPackage::create($item);
        }
    }
}
