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
            $value['code'] = str_replace(' ', '', $value['code']);
            $value['logo'] = asset('images/banklogo/'.$value['code'].'.png');
            $vietqr_banks[] = $value;
        }
        $res = [
            'success' => true,
            'data' => $vietqr_banks,
        ];
        return $res;
    }
}
