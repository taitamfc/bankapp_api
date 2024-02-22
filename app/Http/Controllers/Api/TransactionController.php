<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Http\Resources\TransactionResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RechargeRequest;
use App\Http\Requests\EarnMoneyRequest;
use App\Notifications\EarnMoneyNotification;
use App\Models\User;


class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = new Transaction;
        $items = $query->paginate(5);
        $transactionCollection = TransactionResource::collection($items);
        return response()->json([
            'success' => true,
            'data' => $transactionCollection,
        ]);
    }

    public function recharge(RechargeRequest $request)
    {
        $user_id = Auth::id();
        $transaction = new Transaction;
        $transaction->reference = 2;
        $transaction->amount = $request->amount;
        $transaction->received = $request->amount;
        $transaction->type = 'RECHARGE';
        $transaction->type_money = 'VND';
        $transaction->status = 0;
        $transaction->user_id = $user_id;
        $transaction->bank_name = $request->bank_name;
        $transaction->bank_number = $request->bank_number;
        $transaction->bank_user = $request->bank_user;
        $transaction->save();
        $res = [
            'success' => true,
            'message' => 'Nạp tiền thành công!',
            'data' => $transaction
        ];
        return response()->json($res, 200);
    }

    public function listRecharge(Request $request)
    {
        $query = Transaction::where('type','RECHARGE');
        
        if($request && $request->search){
            $query->where('amount', 'LIKE', '%' . $request->search . '%');
        }
        $items = $query->paginate(5);
        return $items;
        $res = [
            'success' => true,
            'message' => 'Danh sách nạp tiền!',
            'data' => $items,
        ];
        return $res;
    }

    public function paymentEarnMoney(EarnMoneyRequest $request)
    {
        $user_id = Auth::id();
        $transaction = new Transaction;
        $transaction->reference = 3;
        $transaction->amount = $request->amount;
        $transaction->received = $request->amount;
        $transaction->type = 'EARNMONEY';
        $transaction->type_money = 'VND';
        $transaction->status = 0;
        $transaction->user_id = $user_id;
        $transaction->bank_name = $request->bank_name;
        $transaction->bank_number = $request->bank_number;
        $transaction->bank_user = $request->bank_user;
        $transaction->verify_code = $request->verify_code;
        $transaction->save();
        $res = [
            'success' => true,
            'message' => 'Yêu cầu rút tiền đã được gửi đi thành công!',
            'data' => $transaction
        ];
        return response()->json($res, 200);
    }

    public function listEarnMoney(Request $request)
    {
        $query = Transaction::where('type','EARNMONEY');
        
        if($request && $request->search){
            $query->where('amount', 'LIKE', '%' . $request->search . '%');
        }
        $items = $query->paginate(5);
        return $items;
        $res = [
            'success' => true,
            'message' => 'Danh sách Kiếm tiền!',
            'data' => $items,
        ];
        return $res;
    }

    public function sendMailEarnMoney(Request $request)
    {
        $user = User::where('id', Auth::id())->firstOrFail();
    
        $verify_code = mt_rand(100000, 999999);
        
        $user->notify(new EarnMoneyNotification($verify_code));

        return response()->json([
            'success' => true,
            'message' => 'Mã xác nhận đã được gửi vào Email của bạn!',
        ]);
    }
}
