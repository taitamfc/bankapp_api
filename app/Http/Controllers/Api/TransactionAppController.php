<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionApp;
use App\Models\UserBankAccount;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionAppResource;

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
        $user_current = UserBankAccount::where('id',$request->user_bank_account_id)->first();
        $data = $request->except('_method','_token');
        $data['surplus'] = $user_current->surplus - $data['amount'];
        $item = TransactionApp::create($data);
        $user_current->surplus = $data['surplus'];
        $user_current->save();
        $res = [
            'success' => true,
            'data' => $item,
        ];
        return $res;
    }
}