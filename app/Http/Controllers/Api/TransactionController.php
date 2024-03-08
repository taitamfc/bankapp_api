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
use Illuminate\Support\Facades\Http;
use DB;



class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page', 1); // Trang mặc định là 1 nếu không được truyền vào
        $perPage = $request->input('perPage', 5); // Số lượng mục dữ liệu mỗi trang mặc định là 
        $query = new Transaction;
        if ($request->search) {
            $query = $query->where('type', 'LIKE', '%' . $request->search . '%');
        }
        $items = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        $transactionCollection = TransactionResource::collection($items);
        return response()->json([
            'success' => true,
            'data' => $transactionCollection,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function deposits(RechargeRequest $request)
    {
        DB::beginTransaction();
        try {
            $user_id = Auth::guard('api')->id();
            $transaction = new Transaction;
            $transaction->reference = 2;
            $transaction->amount = $request->amount;
            $transaction->received = $request->amount;
            $transaction->type = 'RECHARGE';
            $transaction->type_money = 'VND';
            $transaction->status = 0;
            $transaction->user_id = $user_id;
            $transaction->ownerbank_id = $request->ownerbank_id;
            $transaction->save();

            $ownerBank = OwnerBank::find($request->ownerbank_id);
            $param = [
                "accountNo" => $ownerBank->account_number,
                "accountName"=> $ownerBank->account_name,
                "acqId"=> $ownerBank->bin,
                "amount"=> $request->amount,
                "addInfo"=> $ownerBank->note,
                "format"=> 'text',
                "template"=> 'compact',
            ];
            $apiCheckUrl = "https://api.vietqr.io/v2/generate";
            $bankApiKey = env('BANK_API_KEY');
            $bankApiClientId = env('BANK_API_CLIENT_ID');
            $response = Http::withHeaders([
            'x-api-key' => $bankApiKey,
            'x-client-id' => $bankApiClientId,
            ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
            $result = $response->json();

            DB::commit();
            $res = [
                'success' => true,
                'message' => 'Nạp tiền thành công!',
                'data' => $transaction,
                'QR_URI_data' => $result,
            ];
            return response()->json($res, 200);
            
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        } 
    }

    public function depositsDetail(Request $request) {
        $query = Transaction::where('type','RECHARGE');
        
        if($request && $request->id){
            $query->where('id', $request->id);
        }
        $items = $query->first();
        $res = [
            'success' => true,
            'message' => 'chi tiết nạp tiền',
            'data' => $items,
        ];
        return $res;
    }



    public function listDeposits(Request $request)
    {
        $page = $request->input('page', 1); // Trang mặc định là 1 nếu không được truyền vào
        $perPage = $request->input('perPage', 5);
        $query = Transaction::where('type','RECHARGE');
        if($request && $request->search){
            $query->where('amount', 'LIKE', '%' . $request->search . '%');
        }
        $items = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        $transactionCollection = TransactionResource::collection($items);
        $res = [
            'success' => true,
            'message' => 'Danh sách nạp tiền!',
            'data' => $transactionCollection,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ];
        return $res;
    }

    public function withdraw(EarnMoneyRequest $request)
    {
        $user_id = Auth::guard('api')->id();
        $verify_code = VerifyCode::where('user_id', $user_id)
                        ->where('type', 'EARNMONEY')
                        ->orderBy('id', 'desc')
                        ->first();
        if($verify_code == null){
            $res = [
                'success' => false,
                'data' => 'Vui lòng lấy mã xác nhận trước khi thực hiện giao dịch!',
            ];
            return response()->json($res);
        }
        $code = $verify_code->code;
        if ($request->verify_code == $code) {
            DB::beginTransaction();
            try {
                $user = User::findOrFail($user_id);

                if ($request->amount <= $user->account_balance) {
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
    
                    $user->account_balance -= $transaction->amount;
                    $user->save();
                }else {
                    $res = [
                        'success' => false,
                        'data' => 'Số dư không đủ!',
                    ];
                    return response()->json($res, 200);
                }
                
                DB::commit();
                $res = [
                    'success' => true,
                    'message' => 'Yêu cầu rút tiền đã được gửi đi thành công!',
                    'data' => $transaction
                ];
                return response()->json($res, 200);
                
            } catch (Exception $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }else {
            $res = [
                'success' => false,
                'data' => 'Mã xác nhận sai, vui lòng kiểm tra lại!',
            ];
            return response()->json($res);
        }
    }

    public function paymentWithdraw(Request $request)
    {
        $page = $request->input('page', 1); // Trang mặc định là 1 nếu không được truyền vào
        $perPage = $request->input('perPage', 5); // Số lượng mục dữ liệu mỗi trang mặc định là 
        $query = Transaction::where(function ($query) {
            $query->where('type', 'EARNMONEY')
                ->orWhere('type', 'PAYMONEY');
        });
        if ($request && $request->search) {
            $query = $query->where('type', 'LIKE', '%' . $request->search . '%');
        }
        $items = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);
        $transactionCollection = TransactionResource::collection($items);
        // return $items;
        $res = [
            'success' => true,
            'message' => 'Danh sách Kiếm tiền!',
            'data' => $transactionCollection,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ];
        return $res;
    }

    public function indexEarnMoney()
    {
        $user = Auth::guard('api')->user();
        $data = 
            (object) [
                "can_earn_money" => 0,
                "count_account_chilrent" => 0,
                "count_people" => 0,
                "total_earn_money" => 0,
                "total_profit" => 10,
                "count_withdraw_money" => 0,
                "total_withdraw_money" => 0,
                "user_name" => $user->user_name,
            ]
        ;
        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function transfer(TransferRequest $request)
    {
        $user_id = Auth::guard('api')->id();
        $verify_code = VerifyCode::where('user_id', $user_id)
                        ->where('type', 'PAYMONEY')
                        ->orderBy('id', 'desc')
                        ->first();
        if($verify_code == null){
            $res = [
                'success' => false,
                'data' => 'Vui lòng lấy mã xác nhận trước khi thực hiện giao dịch!',
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
                'data' => 'Mã xác nhận sai, vui lòng kiểm tra lại!',
            ];
            return response()->json($res);
        }
    }

    public function sendMail(Request $request)
    {
        $user = User::where('id', Auth::guard('api')->id())->firstOrFail();
        if ($request->type == "EARNMONEY") {
            $code = mt_rand(100000, 999999);       
            $verify_code = new VerifyCode;
            $verify_code->type = 'EARNMONEY';
            $verify_code->code = $code;
            $verify_code->user_id = Auth::guard('api')->id();
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
            $verify_code->user_id = Auth::guard('api')->id();
            $verify_code->save();
            $user->notify(new PayMoneyNotification($code));
            return response()->json([
                'success' => true,
                'message' => 'Mã xác nhận chuyển tiền đã được gửi vào Email của bạn!',
            ]);
        }
    }
}
