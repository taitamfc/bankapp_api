<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;


class HomeController extends Controller
{
    public function index()
    {
        $data = [
            "count_deposit" => 0,
            "count_paymoney" => 0,
            "total_money_pay" => 0,
            "total_money_check" => 0,
            "count_openbank" => 0,
            "count_bill_paymoney" => 0,
            "count_open_account" => 0,
            "count_bill_account_balance" => 0,
            "count_account_chilrent" => 0,
            "count_bill_fluctuations" => 0,
            "count_account_chilrent_deposit" => 0,
            "total_money_create_bill_chilrent" => 0,
        ];
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function dataHeader(){
        $user = User::findOrFail(Auth::guard('api')->id());
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }
}
