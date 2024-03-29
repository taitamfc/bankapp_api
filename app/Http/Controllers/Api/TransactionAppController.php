<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionApp;
use App\Models\Transaction;
use App\Models\UserBankAccount;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionAppResource;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Requests\TranferAppRequest;

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
    public function transfer(TranferAppRequest $request){
        $user = User::find(Auth::guard('api')->id());
        if ($user->account_balance < 55000 ) {
            $res = [
                'success' => false,
                'data' => "Số dư ở web không đủ để trừ phí khi chuyển tiền trong App, Vui lòng nạp tiền vào web!",
            ];
            return $res;
        }
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
            if ($user_current->type == "VCB") {
                $data['bank_name'] = "Ngân hàng Ngoại thương Việt Nam (Vietcombank)";
            }else{
                $data['bank_name'] = "Ngân hàng TMCP Kỹ thương Việt Nam (Techcombank)";
            }
            if ($user_current->type == "VCB") {
                $randomNumberVCB = mt_rand(100000000, 999999999);
                $data['transaction_code'] = "5".$randomNumberVCB; // tự động random
            }else{
                $randomNumber = mt_rand(100000000000, 999999999999);
                $data['transaction_code'] = "FT23".$randomNumber; // tự động random
            }
            $data['user_bank_account_id'] = $user_current->id;
            $data['from_name'] = $user_current->bank_username;
            $data['from_number'] = $user_current->bank_number;
            $data['account_balance'] = $user_current->account_balance;
            $data['type'] = "TRANSFER";
            $data['received_amount'] = $data['amount'];
            $data['fee_amount'] = 0;
            $item = TransactionApp::create($data);

            $user->account_balance -= 55000;
            $user->save();
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
        $user = User::find(Auth::guard('api')->id());
        if ($user->account_balance < ($request->amount +($request->amount/100)*1 ) ) {
            $res = [
                'success' => false,
                'data' => "Số dư không đủ để nạp vào App",
            ];
            return $res;
        }
        DB::beginTransaction();
        try {
            $user_bank_account = UserBankAccount::where('id',$request->user_bank_account_id)->first();
            $account_balance = ($request->amount*100) + $user_bank_account->account_balance;
            $user_bank_account->account_balance = $account_balance;
            $user_bank_account->save();

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

            $user = User::findOrFail(Auth::guard('api')->id());
            $user->account_balance -= ($request->amount +(($request->amount/100)*1 ));
            $user->save();
            DB::commit();
            $res = [
                'success' => true,
                'data' => $user_bank_account,
                'transactionWeb' => $transaction,
            ];
            return $res;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

}