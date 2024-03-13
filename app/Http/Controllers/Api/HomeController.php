<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\Transaction;
use App\Models\TransactionApp;

class HomeController extends Controller
{
    public function index(){
        try {
            $deposits = Transaction::whereType('RECHARGE')->whereUser_id(Auth::guard('api')->id())->get();
            $count_deposit = 0;
            foreach ($deposits as $deposit) {
                $count_deposit += $deposit->received;
            }
            $count_paymoneys = Transaction::whereType('DEPOSITAPP')->whereUser_id(Auth::guard('api')->id())->get();
            $paymoneys = 0;
            foreach ($count_paymoneys as $paymoney) {
                $paymoneys += $paymoney->received;
            }
            $total_money_pays = TransactionApp::whereType('RECEIVE')->whereUser_bank_account_id(Auth::guard('api')->id())->get();
            $money_pays = 0;
            foreach ($total_money_pays as $paymoney) {
                $money_pays += $paymoney->amount;
            }
            $data = [
                "count_deposit" => number_format($count_deposit),
                "count_paymoney" => number_format($paymoneys),
                "total_money_pay" => $money_pays,
                "total_money_check" => 0,
                "count_openbank" => UserBankAccount::whereUser_id(Auth::guard('api')->id())->count(),
                "count_bill_paymoney" => $count_paymoneys->count(),
                "count_open_account" => UserBankAccount::whereUser_id(Auth::guard('api')->id())->count(),
                "count_bill_account_balance" => Transaction::count(),
                "count_account_chilrent" => UserBankAccount::whereUser_id(Auth::guard('api')->id())->count(),
                "count_bill_fluctuations" => Transaction::count(),
                "count_account_chilrent_deposit" => UserBankAccount::whereUser_id(Auth::guard('api')->id())->count(),
                "total_money_create_bill_chilrent" => Transaction::count(),
            ];
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->getMessage(),
            ]);
        }
    }

    public function dataHeader(){
        $user = User::findOrFail(Auth::guard('api')->id());
        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }
}