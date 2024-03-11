<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserBankAccount;
use App\Models\Transaction;
use App\Models\TransactionApp;

class HomeAdminController extends Controller
{
    public function index(){
        try {
            $deposits = Transaction::whereType('RECHARGE')->get();
            $count_deposit = 0;
            foreach ($deposits as $deposit) {
                $count_deposit += $deposit->received;
            }
            $count_paymoneys = Transaction::whereType('DEPOSITAPP')->get();
            $paymoneys = 0;
            foreach ($count_paymoneys as $paymoney) {
                $paymoneys += $paymoney->received;
            }
            $total_money_pays = TransactionApp::whereType('RECEIVE')->get();
            $money_pays = 0;
            foreach ($total_money_pays as $paymoney) {
                $money_pays += $paymoney->amount;
            }
            $data = [
                "count_deposit" => $count_deposit,
                "count_paymoney" => $paymoneys,
                "total_money_pay" => $money_pays,
                "total_money_check" => 0,
                "count_openbank" => UserBankAccount::all()->count(),
                "count_bill_paymoney" => $count_paymoneys->count(),
                "count_open_account" => UserBankAccount::all()->count(),
                "count_bill_account_balance" => Transaction::count(),
                "count_account_chilrent" => UserBankAccount::all()->count(),
                "count_bill_fluctuations" => Transaction::count(),
                "count_account_chilrent_deposit" => UserBankAccount::all()->count(),
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
}

