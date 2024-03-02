<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionApp;
use App\Models\Transaction;
use App\Models\UserBankAccount;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionAppResource;
use DB;
use Illuminate\Support\Facades\Auth;

class TransactionAppController extends Controller
{
    public function index()
    {
        $data = TransactionAppResource::collection(TransactionApp::orderBy('id', 'desc')->paginate(5));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
    public function transfer(Request $request){
        DB::beginTransaction();
        try {
            $data = $request->except('_method','_token');
            $user_current = UserBankAccount::where('user_id',Auth::guard('api')->id())->where('type', $request->type)->first();
            if ($user_current->account_balance >= $data['amount']) {
                $user_current->account_balance -= $data['amount'];
            }else{
                $res = [
                    'success' => false,
                    'error' => "số dư không đủ!",
                ];
                return $res;
            }
            $user_current->save();

            $data['transaction_code'] = "TF".$data['bank_code_id'].".".time(); // tự động random
            $data['user_bank_account_id'] = $user_current->id;
            $data['from_name'] = $user_current->bank_username;
            $data['from_number'] = $user_current->bank_number;
            $data['account_balance'] = $user_current->account_balance;
            $data['type'] = "TRANSFER";
            $data['received_amount'] = $data['amount'];
            $data['fee_amount'] = 0;
            $item = TransactionApp::create($data);
            DB::commit();
            $res = [
                'success' => true,
                'data' => [
                    'account_info' => $user_current,
                    'transaction_info' => $item,
                ]
            ];
            return $res;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
        
    }
    public function depositApp(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_bank_account = UserBankAccount::where('id',$request->user_bank_account_id)->first();
            $account_balance = $request->amount + $user_bank_account->account_balance;
            $user_bank_account->account_balance = $account_balance;
            $user_bank_account->save();
            // lưu vào lịch sử app
            $transaction_app_deposit = new TransactionApp;
            $transaction_app_deposit->user_bank_account_id = $user_bank_account->id;
            $transaction_app_deposit->type = "RECEIVE";
            $transaction_app_deposit->reference = "123cfd456";
            $transaction_app_deposit->from_name = "BankWeb";
            $transaction_app_deposit->recipient_name = $user_bank_account->bank_username;
            $transaction_app_deposit->recipient_account_number = $user_bank_account->bank_number;
            $transaction_app_deposit->amount = $request->amount;
            $transaction_app_deposit->account_balance = $user_bank_account->account_balance;
            $transaction_app_deposit->note = "Nạp tiền từ tài khoản Web";
            $transaction_app_deposit->save();

            // lưu vào lịch sử web
            $user_id = Auth::guard('api')->id();
            $transaction = new Transaction;
            $transaction->reference = 6;
            $transaction->amount = $request->amount;
            $transaction->received = $request->amount;
            $transaction->type = 'DEPOSITAPP';
            $transaction->type_money = 'VND';
            $transaction->status = 1;
            $transaction->user_id = $user_id;
            $transaction->note = "Nạp tiền vào App";
            $transaction->save();
            DB::commit();
            $res = [
                'success' => true,
                'data' => $user_bank_account,
                'transactionApp' => $transaction_app_deposit,
                'transactionWeb' => $transaction,
            ];
            return $res;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}