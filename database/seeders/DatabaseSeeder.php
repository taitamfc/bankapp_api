<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(NewSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(BankSeeder::class);
        $this->call(TransactionSeeder::class);
        $this->call(OwnerBankSeeder::class);
        $this->call(BankListSeeder::class);
    }
}
