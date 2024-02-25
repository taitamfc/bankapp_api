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
use App\Notifications\PayMoneyNotification;
use App\Models\User;
use App\Models\VerifyCode;
use App\Models\OwnerBank;
use App\Http\Requests\TransferRequest;

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

    public function deposits(RechargeRequest $request)
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
            $transaction->save();
            $owner_bank = OwnerBank::where('id',$request->ownerbank_id)->first();
            $res = [
                'success' => true,
                'message' => 'Nạp tiền thành công!',
                'data' => $owner_bank,
            ];
            return response()->json($res, 200);  
    }

    public function listDeposits(Request $request)
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

    public function withdraw(EarnMoneyRequest $request)
    {
        $user_id = Auth::id();
        $verify_code = VerifyCode::where('user_id', $user_id)
                        ->where('type', 'EARNMONEY')
                        ->orderBy('id', 'desc')
                        ->first();
        if($verify_code == null){
            $res = [
                'success' => false,
                'message' => 'Vui lòng lấy mã xác nhận trước khi thực hiện giao dịch!',
            ];
            return response()->json($res);
        }
        $code = $verify_code->code;
        if ($request->verify_code == $code) {
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
            $transaction->save();
            $res = [
                'success' => true,
                'message' => 'Yêu cầu rút tiền đã được gửi đi thành công!',
                'data' => $transaction
            ];
            return response()->json($res, 200);
        }else {
            $res = [
                'success' => false,
                'message' => 'Mã xác nhận sai, vui lòng kiểm tra lại!',
            ];
            return response()->json($res);
        }
    }

    public function paymentWithdraw(Request $request)
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

    public function indexEarnMoney()
    {
        $data = [
            (object) [
                "can_earn_money" => 0,
                "count_account_chilrent" => 0,
                "count_people" => 0,
                "total_earn_money" => 0,
                "total_profit" => "10% / đơn nạp thành công",
                "count_withdraw_money" => 0,
                "total_withdraw_money" => 0,
            ]
        ];
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function transfer(TransferRequest $request)
    {
        $user_id = Auth::id();
        $verify_code = VerifyCode::where('user_id', $user_id)
                        ->where('type', 'PAYMONEY')
                        ->orderBy('id', 'desc')
                        ->first();
        if($verify_code == null){
            $res = [
                'success' => false,
                'message' => 'Vui lòng lấy mã xác nhận trước khi thực hiện giao dịch!',
            ];
            return response()->json($res);
        }
        $code = $verify_code->code;
        if ($request->verify_code == $code) {
            $transaction = new Transaction;
            $transaction->reference = 4;
            $transaction->amount = $request->amount;
            $transaction->received = $request->amount;
            $transaction->type = 'PAYMONEY';
            $transaction->type_money = 'VND';
            $transaction->status = 0;
            $transaction->account_source_id = $request->account_source_id;
            $transaction->account_target_id = $request->account_target_id;
            $transaction->user_id = $user_id;
            $transaction->save();
            $res = [
                'success' => true,
                'message' => 'Yêu cầu chuyển tiền đã được gửi đi thành công!',
                'data' => $transaction,
            ];
            return response()->json($res, 200);
        }else {
            $res = [
                'success' => false,
                'message' => 'Mã xác nhận sai, vui lòng kiểm tra lại!',
            ];
            return response()->json($res);
        }
    }

    public function sendMail(Request $request)
    {
        $user = User::where('id', Auth::id())->firstOrFail();
        if ($request->type == "EARNMONEY") {
            $code = mt_rand(100000, 999999);       
            $verify_code = new VerifyCode;
            $verify_code->type = 'EARNMONEY';
            $verify_code->code = $code;
            $verify_code->user_id = Auth::id();
            $verify_code->save();
            $user->notify(new EarnMoneyNotification($code));
            return response()->json([
                'success' => true,
                'message' => 'Mã xác nhận đã được gửi vào Email của bạn!',
            ]);
        }
        if ($request->type == "PAYMONEY") {
            $code = mt_rand(100000, 999999);       
            $verify_code = new VerifyCode;
            $verify_code->type = 'PAYMONEY';
            $verify_code->code = $code;
            $verify_code->user_id = Auth::id();
            $verify_code->save();
            $user->notify(new PayMoneyNotification($code));
            return response()->json([
                'success' => true,
                'message' => 'Mã xác nhận chuyển tiền đã được gửi vào Email của bạn!',
            ]);
        }
    }
}
