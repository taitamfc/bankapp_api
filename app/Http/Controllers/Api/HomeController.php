<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Transaction;


class HomeController extends Controller
{
    public function index()
    {
        $deposits = Transaction::whereType('DEPOSITAPP')->whereUser_id(Auth::id)->get();
        foreach ($deposits as $deposit) {
            $count_deposit += $deposit->received;
        }
        $data = [
            "count_deposit" => $count_deposit,
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