<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DepositHistory;

class DepositHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DepositHistory::create([
            'user_id' => 1,
            'reference' => 'REF123',
            'amount' => 100.00,
            'received' => 100.00,
            'status' => 1,
            'note' => 'Successful deposit',
        ]);
    }
}
