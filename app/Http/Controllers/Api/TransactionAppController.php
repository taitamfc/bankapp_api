<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionApp;
use App\Models\UserBankAccount;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionAppResource;
use DB;
use Illuminate\Support\Facades\Auth;

class TransactionAppController extends Controller
{
    public function index()
    {
        $data = TransactionAppResource::collection(TransactionApp::all());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
    public function transfer(Request $request){
        $data = $request->except('_method','_token');
        $user_current = UserBankAccount::whereUser_id(Auth::guard('api')->id())->whereType($data['from_bank'])->first();
        $user_recieve = UserBankAccount::whereBank_number($data['to_number'])->whereType($data['to_bank'])->first();
        if ($user_current->surplus >= $data['amount']) {
            $user_current->surplus -= $data['amount'];
            $user_recieve->surplus += $data['amount'];
            $user_current->save();
            $user_recieve->save();
        }
        $data['user_bank_account_id'] = $user_current->id;
        $data['to_name'] = $user_recieve->bank_username;
        $data['from_name'] = $user_current->bank_username;
        $data['from_number'] = $user_current->bank_number;
        $data['surplus'] = $user_current->surplus;
        $data['type'] = "TRANSFER";
        $item = TransactionApp::create($data);
        $user_current->save();
        $res = [
            'success' => true,
            'data' => $item,
        ];
        return $res;
    }
    public function depositApp(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_bank_account = UserBankAccount::findOrFail($request->user_bank_account_id);
            $surplus = $request->amount + $user_bank_account->surplus;
            $user_bank_account->surplus = $surplus;
            $user_bank_account->save();

            $transaction_app_deposit = new TransactionApp;
            $transaction_app_deposit->

            $res = [
                'success' => true,
                'data' => $user_bank_account,
            ];
            return $res;
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
}