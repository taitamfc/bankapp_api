<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Package;
class PackAgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Vip 1',
                'price' => 3000000,
                'max_create_account' => 1,
                'max_edit_account' => 2,
                'max_transfer_free' => 5,
                'max_deposit_app' => 1000000000,
                'type' => 'vip1',
                'bank_code' => 'VCB',
            ],
            [
                'name' => 'Vip 3',
                'price' => 27000000,
                'max_create_account' => 10,
                'max_edit_account' => 20,
                'max_transfer_free' => -1,
                'max_deposit_app' => -1,
                'type' => 'vip3',
                'bank_code' => 'VCB',
            ],
            [
                'name' => 'Vip 2',
                'price' => 9000000,
                'max_create_account' => 3,
                'max_edit_account' => 6,
                'max_transfer_free' => 50,
                'max_deposit_app' => 9000000000,
                'type' => 'vip2',
                'bank_code' => 'VCB',
            ],
            [
                'name' => 'Vip 1',
                'price' => 3000000,
                'max_create_account' => 1,
                'max_edit_account' => 2,
                'max_transfer_free' => 5,
                'max_deposit_app' => 1000000000,
                'type' => 'vip1',
                'bank_code' => 'TCB',
            ],
            [
                'name' => 'Vip 3',
                'price' => 27000000,
                'max_create_account' => 10,
                'max_edit_account' => 20,
                'max_transfer_free' => -1,
                'max_deposit_app' => -1,
                'type' => 'vip3',
                'bank_code' => 'TCB',
            ],
            [
                'name' => 'Vip 2',
                'price' => 9000000,
                'max_create_account' => 3,
                'max_edit_account' => 6,
                'max_transfer_free' => 50,
                'max_deposit_app' => 9000000000,
                'type' => 'vip2',
                'bank_code' => 'TCB',
            ],
        ];

        foreach ($packages as $package) {
            Package::create([
                'name' => $package['name'],
                'price' => $package['price'],
                'max_create_account' => $package['max_create_account'],
                'max_edit_account' => $package['max_edit_account'],
                'max_transfer_free' => $package['max_transfer_free'],
                'max_deposit_app' => $package['max_deposit_app'],
                'type' => $package['type'],
                'bank_code' => $package['bank_code'],
            ]);
        }
    }
}
