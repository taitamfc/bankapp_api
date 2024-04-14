<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BankInfoBillResource;
use App\Models\BankInfoBill;



class BankInfoBillController extends Controller
{
    public function index()
    {
        $data = BankInfoBillResource::collection(BankInfoBill::all());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
}
