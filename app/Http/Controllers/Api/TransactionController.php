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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayOS\PayOS;
use Exception;


class TransactionController extends Controller
{
    // Lịch sử giao dịch
    public function index(Request $request)
    {
        $page = $request->input('page', 1); // Trang mặc định là 1 nếu không được truyền vào
        $perPage = $request->input('perPage', 5); // Số lượng mục dữ liệu mỗi trang mặc định là 
        $query = Transaction::where('user_id', Auth::guard('api')->id());
        if ($request->search) {
            $search_date = $request->search;
            $start_date = $search_date['start_date'];
            $end_date = $search_date['end_date'];
            if( $start_date ){
                $query->whereDate('created_at', '>=', $start_date);
            }
            if( $end_date ){
                $query->whereDate('created_at', '<=', $end_date);
            }
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

    // history admin
    public function historyAdmin(Request $request)
    {
        $page = $request->input('page', 1); // Trang mặc định là 1 nếu không được truyền vào
        $perPage = $request->input('perPage', 5); // Số lượng mục dữ liệu mỗi trang mặc định là 
        $query = Transaction::where('type', 'EARNMONEY');
        if ($request->search) {
            $search_date = $request->search;
            $start_date = $search_date['start_date'];
            $end_date = $search_date['end_date'];
            $name_user = $search_date['name'];
            if( $start_date ){
                $query->whereDate('created_at', '>=', $start_date);
            }
            if( $end_date ){
                $query->whereDate('created_at', '<=', $end_date);
            }
            if( $name_user ){
                $query->whereHas('user', function ($query) use ($name_user) {
                    $query->where('name', 'LIKE', '%' . $name_user . '%');
                });
            }
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

    public function show($id){
        try {
            $item = Transaction::findOrfail($id);
            return response()->json([
                'success' => true,
                'data' => $item
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'data' => $e->getMessage()
            ]);
        }
    }

    public function updateStatus($id,Request $request){
        DB::beginTransaction();
        try {
            $item = Transaction::findOrfail($id);
            if (($item->type =='EARNMONEY') && ($request->status == 1)) {
                $user = User::find($item->user_id);
                $user->referral_account_balance -= $item->amount;
                $user->save();
            }
            $item->update(['status' => $request->status]);
            DB::commit();
            return response()->json([
                'success' => true,
                'data' =>  $item
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'data' => $e->getMessage()
            ]);
        }
    }

    public function deposit(RechargeRequest $request)
    {
        DB::beginTransaction();
        try {
            $user_id = Auth::guard('api')->id();
            $transaction = new Transaction;
            $transaction->reference = intval(substr(strval(microtime(true) * 10000), -6));
            $transaction->amount = $request->amount;
            $transaction->received = $request->amount;
            $transaction->type = 'RECHARGE';
            $transaction->type_money = 'VND';
            $transaction->status = 0;
            $transaction->user_id = $user_id;
            $transaction->ownerbank_id = $request->ownerbank_id;
            $transaction->save();

            // $ownerBank = OwnerBank::find($request->ownerbank_id);
            // $param = [
            //     "accountNo" => $ownerBank->account_number,
            //     "accountName"=> $ownerBank->account_name,
            //     "acqId"=> $ownerBank->bin,
            //     "amount"=> $request->amount,
            //     "addInfo"=> $ownerBank->note,
            //     "format"=> 'text',
            //     "template"=> 'compact',
            // ];
            DB::commit();
            
            $payOS = new PayOS(
                env('PAYOS_CLIENT_ID'), 
                env('PAYOS_API_KEY'),
                env('PAYOS_CHECKSUM_KEY')
            );

            $data = [
                "orderCode" => $transaction->reference,
                "amount" => (int)$request->amount,
                "description" => "Nạp tiền vào tài khoản",
                "items" => [
                    [
                        "name" => "Nạp tiền vào tài khoản",
                        "quantity" => 1,
                        "price" => (int)$request->amount
                    ]
                ],
                "returnUrl" => route('transactions.handle_return'),
                "cancelUrl" => route('transactions.handle_cancel'),
            ];
            
            try {
                $response = $payOS->createPaymentLink($data);
                $res = [
                    'success' => true,
                    'redirect' => $response['checkoutUrl'],
                    'id' => $transaction->id,
                ];
                return response()->json($res, 200);
            } catch (Exception $e) {
                $res = [
                    'success' => false,
                    'msg' => $e->getMessage()
                ];
                return response()->json($res, 200);
            }

            // $apiCheckUrl = "https://api.vietqr.io/v2/generate";
            // $bankApiKey = env('BANK_API_KEY');
            // $bankApiClientId = env('BANK_API_CLIENT_ID');
            // $response = Http::withHeaders([
            // 'x-api-key' => $bankApiKey,
            // 'x-client-id' => $bankApiClientId,
            // ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
            // $result = $response->json();

            // $res = [
            //     'success' => true,
            //     'message' => 'Nạp tiền thành công!',
            //     'data' => $transaction,
            //     'QR_URI_data' => $result,
            // ];
            // return response()->json($res, 200);
            
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        } 
    }

    public function depositsDetail(Request $request) {
        $items = Transaction::find($request->id);
        
        // if($request && $request->id){
        //     $query->where('id', $request->id);
        // }
        // $items = $query->first();
        $res = [
            'success' => true,
            'message' => 'chi tiết nạp tiền',
            'data' => $items,
        ];
        return $res;
    }


    // Lịch sử nạp tiền
    public function depositHistory(Request $request)
    {
        $page = $request->input('page', 1); // Trang mặc định là 1 nếu không được truyền vào
        $perPage = $request->input('perPage', 5);
        $query = Transaction::where('user_id', Auth::guard('api')->id())
                            ->where(function ($query) {
                                $query->where('type', 'RECHARGE')
                                    ->orWhere('type', 'DEPOSITFROMADMIN');
                            });
        if ($request->search) {
            $search_date = $request->search;
            $start_date = $search_date['start_date'];
            $end_date = $search_date['end_date'];
            if( $start_date ){
                $query->whereDate('created_at', '>=', $start_date);
            }
            if( $end_date ){
                $query->whereDate('created_at', '<=', $end_date);
            }
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

                if ($request->amount <= $user->referral_account_balance) {
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
    
                }else {
                    $res = [
                        'success' => false,
                        'data' => 'Số dư giới thiệu không đủ!',
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
    // Lịch Sử Rút Tiền Và Chuyển Tiền
    public function withdrawHistory(Request $request)
    {
        $page = $request->input('page', 1); // Trang mặc định là 1 nếu không được truyền vào
        $perPage = $request->input('perPage', 5); // Số lượng mục dữ liệu mỗi trang mặc định là 
        $query = Transaction::where(function ($query) {
            $query->where('type', 'EARNMONEY')
                ->orWhere('type', 'PAYMONEY');
        });
        $query = $query->where('user_id', Auth::guard('api')->id());
        if ($request->search) {
            $search_date = $request->search;
            $start_date = $search_date['start_date'];
            $end_date = $search_date['end_date'];
            if( $start_date ){
                $query->whereDate('created_at', '>=', $start_date);
            }
            if( $end_date ){
                $query->whereDate('created_at', '<=', $end_date);
            }
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
        $count_chilrent = User::where('referral_code', $user->user_name)->count();
        $chilrent_account = User::where('referral_code', $user->user_name)->get();
        $count_account_deposit = 0;
        foreach ($chilrent_account as $key => $value) {
            $IsDeposit = Transaction::where('user_id',$value->id)->where('type','RECHARGE')->first();
            if($IsDeposit){
                $count_account_deposit += 1 ;
            }
        }
        $count_withdraw_money = Transaction::where('user_id',Auth::guard('api')->id())->where('type','EARNMONEY')->where('status',1)->count();
        $total_withdraw_money = Transaction::where('user_id',Auth::guard('api')->id())->where('type','EARNMONEY')->where('status',1)->sum('amount');
        $total_earn_money = $user->referral_account_balance + $total_withdraw_money;
        $data = 
            (object) [
                "can_earn_money" => number_format($user->referral_account_balance),
                "count_account_chilrent" => $count_chilrent,
                "count_people" => $count_account_deposit,
                "total_earn_money" => number_format($total_earn_money),
                "total_profit" => 10,
                "count_withdraw_money" => $count_withdraw_money,
                "total_withdraw_money" => number_format($total_withdraw_money),
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
        DB::beginTransaction();
        try {
            if ($request->verify_code == $code) {
                $user = User::find(Auth::guard('api')->id());
                if ($request->amount <= $user->referral_account_balance) {

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
                    
                    $user->referral_account_balance -= $request->amount;
                    $user->account_balance += $request->amount;
                    $user->save();

                    DB::commit();             
                    $res = [
                        'success' => true,
                        'message' => 'Yêu cầu chuyển tiền đã được gửi đi thành công!',
                        'data' => $transaction,
                    ];
                    return response()->json($res);
                }else{
                    $res = [
                        'success' => false,
                        'data' => 'Số dư giới thiệu không đủ!',
                    ];
                    return response()->json($res,200);
                }
            }else {
                $res = [
                    'success' => false,
                    'data' => 'Mã xác nhận sai, vui lòng kiểm tra lại!',
                ];
                return response()->json($res);
            }
        } 
        catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
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

    // Xử lý khi payos trả về
    public function handle_return(Request $request){
        $transaction = Transaction::where('reference',$request->orderCode)->first();
        $user = User::find( $transaction->user_id );

        $transaction->update([
            'status' => 1
        ]);

         // Cộng 10% số tiền nạp tiền cho tài khoản giới thiệu
         if($user){
            $parent_user = User::where('user_name',$user->referral_code)->first();
            if( $parent_user ){
                $pr_referral_account_balance = $parent_user->referral_account_balance;
                $parent_user->referral_account_balance = (float)$pr_referral_account_balance + ($transaction->received/100*10);
                $parent_user->save();
            }
        }

        return view('transactions.handle_return');
    }
    public function handle_cancel(Request $request){
        Transaction::where('reference',$request->orderCode)->update([
            'status' => -1
        ]);
        return view('transactions.handle_cancel');
    }

    public function handleACBReturn(Request $request)
    {
        Log::info("-- WEBHOOK PAYMENT ACB ---");
        $params = $request->all();


        if (!data_get($params, 'status')) {
            Log::info("Status: " . data_get($params, 'status'));
            $response = [
                "status" => true,
                "msg" => "Status is false"
            ];
            echo json_encode($response);
            return;
        }

        $data = data_get($params, 'data');
        if (empty($data)){
            Log::info("Data: " . json_encode($data));
            $response = [
                "status" => true,
                "msg" => "Data is invalid". json_encode($data)
            ];
            echo json_encode($response);
            return;
        };

        $lastTransaction = $data[0];

        if (empty($lastTransaction['type']) || $lastTransaction['type'] !== 'IN') {
            Log::info("Type: " . $lastTransaction['type'] ?? "NULL");
            return response()->json([
                'success' => false,
                'msg' => "Type is empty",
            ]);
        }

        $pattern = '/okbill\s+(\S+)\s+/';


        if (preg_match($pattern, strtolower($lastTransaction['description']), $matches)) {
            $username = trim($matches[1]) ?? "";

            if (empty($username)) {
                Log::info("Username is empty");
                $response = [
                    "status" => true,
                    "msg" => "Username is empty"
                ];
                echo json_encode($response);
                return;
            }

            $user = User::where('user_name', $username)->first();
            if (empty($user)) {
                Log::info("Username is not exist: " . $username);
                $response = [
                    "status" => true,
                    "msg" => "Username is not exist ".$username
                ];
                echo json_encode($response);
                return;
            }

            $transactionId = $lastTransaction['transactionID'] ?? "";
            $check = Transaction::where('reference', $transactionId)->where('user_id', $user->id)->exists();
            if ($check) {
                $response = [
                    "status" => true,
                    "msg" => "Transaction has exists"
                ];
                echo json_encode($response);
                return;
            }

            DB::beginTransaction();
            try {
                $transaction = new Transaction;
                $transaction->reference = $lastTransaction['transactionID'];
                $transaction->amount = $lastTransaction['amount'];
                $transaction->received = $lastTransaction['amount'];
                $transaction->type = 'RECHARGE';
                $transaction->type_money = 'VND';
                $transaction->status = 1;
                $transaction->user_id = $user->id;
                $transaction->ownerbank_id = 2;
                $transaction->save();

                $pre_balance = $user->account_balance;
                $user->account_balance += (float)$pre_balance + ($lastTransaction['amount']);
                $user->save();

                if (!empty($user->referral_code)) {
                    $parent_user = User::where('user_name', $user->referral_code)->first();
                    if ($parent_user) {
                        $pr_referral_account_balance = $parent_user->account_balance;
                        $parent_user->account_balance = (float)$pr_referral_account_balance + ($transaction->received);
                        $parent_user->save();
                    }
                }

                DB::commit();
                $response = [
                    "status" => true,
                    "msg" => "Nạp tiền thành công"
                ];
                echo json_encode($response);
                return;
            } catch (\Exception $exception) {
                DB::rollBack();
                Log::error($exception->getMessage());
                $response = [
                    "status" => true,
                    "msg" =>  $exception->getMessage()
                ];
                echo json_encode($response);
                return;
            }

        }

        $response = [
            "status" => true,
            "msg" =>  'error'
        ];
        echo json_encode($response);
    }
}