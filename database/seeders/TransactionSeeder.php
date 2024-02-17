<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Transaction::create([
            'reference' => '111',
            'type' => 'OPEN_BANK',
            'type_money' => null,
            'amount' => 0,
            'received' => 0,
            'note' => null,
            'user_id' => 1,
            'status' => 1,
        ]);
    }
}
