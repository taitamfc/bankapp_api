<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Http\Resources\VietQRResource;
use App\Models\VietqrBank;

class VietqrBankController extends Controller
{
    public function index(){
        $apiCheckUrl = "https://api.vietqr.io/v2/banks";
        $response = Http::get($apiCheckUrl)->json();
        $vietqr_banks = [];
        foreach ($response['data'] as $key => $value) {
            $vietQR_bank = VietqrBank::where('bin',$value['bin'])->first();
            $value['logo'] = $vietQR_bank->logo;
            $vietqr_banks[] = $value;
        }
        $res = [
            'success' => true,
            'data' => $vietqr_banks,
        ];
        return $res;
    }
}
