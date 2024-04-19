<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\BankNameBill;
use Illuminate\Support\Facades\Http;

class BankNameBillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $bank_bills = ["VCB","TPB","TCB","MB","ACB","ICB","BIDV","VBA","VPB","STB","MSB"];
        $apiCheckUrl = "https://api.vietqr.io/v2/banks";
        $response = Http::get($apiCheckUrl)->json();
        $vietqr_banks = [];
        foreach ($response['data'] as $key => $value) {
            $value['code'] = str_replace(' ', '', $value['code']);
            $value['logo'] = asset('images/banklogo/'.$value['code'].'.png');
            $vietqr_banks[] = $value;
        }
        for ($i=0; $i < count($bank_bills); $i++) { 
            foreach ($vietqr_banks as $key => $item) {
                BankNameBill::create([
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'bin' => $item['bin'],
                    'shortName' => $item['shortName'],
                    'logo' => $item['logo'],
                    'short_name' => $item['short_name'],
                    'type' => $bank_bills[$i],
                    
                ]);
            }
        }
    }
}
