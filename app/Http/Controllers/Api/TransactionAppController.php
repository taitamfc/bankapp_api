<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransactionApp;
use App\Models\Transaction;
use App\Models\UserBankAccount;
use App\Models\UserPackage;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\TransactionAppResource;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Http\Requests\TranferAppRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\CheckVietQR;


class TransactionAppController extends Controller
{
    public function index(Request $request)
    {
        $query = TransactionApp::where('user_bank_account_id', $request->user_bank_account_id);
        $items = $query->orderBy('id', 'desc')->paginate(5);
        $transactionAppCollection = TransactionAppResource::collection($items);
        $res = [
            'success' => true,
            'data' => $transactionAppCollection,
        ];
        return $res;
    }
    public function transfer(TranferAppRequest $request){
        try {
            Log::info( json_encode( $request->toArray() ) );
            $user = User::find(Auth::guard('api')->id());
            $user_bank_account = json_decode($user->active_bank_acount);
            // dd($user_bank_account->bank_number);
            $is_UserPackage = UserPackage::where('user_id',$user->id)->where('bank_code',$request->type)->first();
            // Lấy ngày hôm nay
            $today = Carbon::today();
            // Đếm số lần tạo bản ghi trong ngày hôm nay
            if ($is_UserPackage) {
                $countTransferToday = TransactionApp::whereDate('created_at', $today)->where('bank_code_id', $request->bank_code_id)->where('from_number', $user_bank_account->bank_number)->where('created_at', '>' ,$is_UserPackage->created_at)->count();
                $package = Package::where('type',$is_UserPackage->type_package)->where('bank_code',$request->type)->first();
                if ($package->max_transfer_free == -1) {
                    // xử lí miễn phí
                    $user = User::find(Auth::guard('api')->id());
                    DB::beginTransaction();
                    try {
                        $data = $request->except('_method','_token');
                        $user_current = UserBankAccount::where('user_id',Auth::guard('api')->id())->where('type', $request->type)->where('bank_number', $user_bank_account->bank_number)->first();
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

                        
                        // $CheckVietQR người nhận
                        $CheckVietQR = CheckVietQR::where('bank_acount',$data['recipient_account_number'])->latest('id')->first();
                        $data_api_all_bank_vietQR = CheckVietQR::DATA_BANK_VIETQR;
                        foreach ($data_api_all_bank_vietQR as $key => $value) {
                            if ($value['bin'] == $CheckVietQR->bin) {
                                $name_bank = $value['name'];
                            }
                        }
                        
                        $data['bank_name'] = $name_bank;
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
                }else{
                    if($countTransferToday < $package->max_transfer_free ){
                        // xử lí miễn phí
                        $user = User::find(Auth::guard('api')->id());
                        DB::beginTransaction();
                        try {
                            $data = $request->except('_method','_token');
                            $user_current = UserBankAccount::where('user_id',Auth::guard('api')->id())->where('type', $request->type)->where('bank_number', $user_bank_account->bank_number)->first();
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
                            // $CheckVietQR người nhận
                            $CheckVietQR = CheckVietQR::where('bank_acount',$data['recipient_account_number'])->latest('id')->first();
                            $data_api_all_bank_vietQR = CheckVietQR::DATA_BANK_VIETQR;
                            foreach ($data_api_all_bank_vietQR as $key => $value) {
                                if ($value['bin'] == $CheckVietQR->bin) {
                                    $name_bank = $value['name'];
                                }
                            }
                            
                            $data['bank_name'] = $name_bank;
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

                            $is_UserPackage->total_transfer_app += 1;
                            $is_UserPackage->save();
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
                    }else{
                        // xử lí bình thường
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
                            $user_current = UserBankAccount::where('user_id',Auth::guard('api')->id())->where('type', $request->type)->where('bank_number', $user_bank_account->bank_number)->first();
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
                            // $CheckVietQR người nhận
                            $CheckVietQR = CheckVietQR::where('bank_acount',$data['recipient_account_number'])->latest('id')->first();
                            $data_api_all_bank_vietQR = CheckVietQR::DATA_BANK_VIETQR;
                            foreach ($data_api_all_bank_vietQR as $key => $value) {
                                if ($value['bin'] == $CheckVietQR->bin) {
                                    $name_bank = $value['name'];
                                }
                            }
                            
                            $data['bank_name'] = $name_bank;
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
                            //lưu vào lịch sử
                            $transaction = new Transaction;
                            $transaction->reference = intval(substr(strval(microtime(true) * 10000), -6));
                            $transaction->amount = 55000;
                            $transaction->received = 55000;
                            $transaction->type = 'FEETRANSFER';
                            $transaction->type_money = 'VND';
                            $transaction->status = 1;
                            $transaction->note = 'Trừ tiền phí chuyển tiền ở App';
                            $transaction->user_id = $user->id;
                            $transaction->save();
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
                }
            }else{ 
                // xử lí bình thường
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
                    $user_current = UserBankAccount::where('user_id',Auth::guard('api')->id())->where('type', $request->type)->where('bank_number', $user_bank_account->bank_number)->first();
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
                    // $CheckVietQR người nhận
                    $CheckVietQR = CheckVietQR::where('bank_acount',$data['recipient_account_number'])->latest('id')->first();
                    $data_api_all_bank_vietQR = CheckVietQR::DATA_BANK_VIETQR;
                    foreach ($data_api_all_bank_vietQR as $key => $value) {
                        if ($value['bin'] == $CheckVietQR->bin) {
                            $name_bank = $value['name'];
                        }
                    }
                    
                    $data['bank_name'] = $name_bank;
                    
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
                    //lưu vào lịch sử
                    $transaction = new Transaction;
                    $transaction->reference = intval(substr(strval(microtime(true) * 10000), -6));
                    $transaction->amount = 55000;
                    $transaction->received = 55000;
                    $transaction->type = 'FEETRANSFER';
                    $transaction->type_money = 'VND';
                    $transaction->status = 1;
                    $transaction->note = 'Trừ tiền phí chuyển tiền ở App';
                    $transaction->user_id = $user->id;
                    $transaction->save();
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
        } catch (Exception $e) { 
            Log::error('An error occurred: ' . $e->getMessage()); // Ghi log cho ngoại lệ
        }
        
    }
    public function depositApp(Request $request)
    {
        $user = User::find(Auth::guard('api')->id());
        $user_bank_account = UserBankAccount::where('id',$request->user_bank_account_id)->first();
        $is_UserPackage = UserPackage::where('user_id',$user->id)->where('bank_code',$user_bank_account->type)->first();
        if ($is_UserPackage) {
            $total_amount_deposit = $request->amount + $is_UserPackage->total_deposit_app;
            $package = Package::where('type',$is_UserPackage->type_package)->where('bank_code',$user_bank_account->type)->first();
            if ($package->max_deposit_app == -1) {
                $amount_deposit = $request->amount/100;
                // xử lý miễn phí chiết khấu 80%
                $user = User::find(Auth::guard('api')->id());
                DB::beginTransaction();
                try {
                    $user_bank_account = UserBankAccount::where('id',$request->user_bank_account_id)->first();
                    $account_balance = $request->amount + $user_bank_account->account_balance;
                    $user_bank_account->account_balance = $account_balance;
                    $user_bank_account->save();

                    // lưu vào lịch sử web
                    $user_id = Auth::guard('api')->id();
                    $transaction = new Transaction;
                    $transaction->reference = 6;
                    $transaction->amount = (($amount_deposit/100)*20);
                    $transaction->received = (($amount_deposit/100)*20);
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
                        'transactionWeb' => $transaction,
                    ];
                    return $res;
                } catch (Exception $e) {
                    DB::rollBack();
                    throw new Exception($e->getMessage());
                }
            }else {
                if($total_amount_deposit <= $package->max_deposit_app){
                    $amount_deposit = $request->amount/100;
                    // xử lý miễn phí chiết khấu 80%
                    $user = User::find(Auth::guard('api')->id());
                    DB::beginTransaction();
                    try {
                        $user_bank_account = UserBankAccount::where('id',$request->user_bank_account_id)->first();
                        $account_balance = $request->amount + $user_bank_account->account_balance;
                        $user_bank_account->account_balance = $account_balance;
                        $user_bank_account->save();

                        // lưu vào lịch sử web
                        $user_id = Auth::guard('api')->id();
                        $transaction = new Transaction;
                        $transaction->reference = 6;
                        $transaction->amount = (($amount_deposit/100)*20);
                        $transaction->received = (($amount_deposit/100)*20);
                        $transaction->type = 'DEPOSITAPP';
                        $transaction->type_money = 'VND';
                        $transaction->status = 1;
                        $transaction->user_id = $user_id;
                        $transaction->note = "Nạp tiền vào App";
                        $transaction->save();


                        $is_UserPackage->total_deposit_app += $request->amount;
                        $is_UserPackage->save();

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
                }else{
                    if ($is_UserPackage->total_deposit_app < $package->max_deposit_app) {
                        $money_over_limit = $total_amount_deposit - $package->max_deposit_app;
                        $fee_money_over_limit = ($money_over_limit/10000)*20;
                        // xử lý bình thường
                        $user = User::find(Auth::guard('api')->id());
                        if ($user->account_balance < ($fee_money_over_limit)) {
                            $res = [
                                'success' => false,
                                'data' => "Số dư không đủ để nạp vào App",
                            ];
                            return $res;
                        }
                        DB::beginTransaction();
                        try {
                            $user_bank_account = UserBankAccount::where('id',$request->user_bank_account_id)->first();
                            $account_balance = $request->amount + $user_bank_account->account_balance;
                            $user_bank_account->account_balance = $account_balance;
                            $user_bank_account->save();
    
                            // lưu vào lịch sử web
                            $user_id = Auth::guard('api')->id();
                            $transaction = new Transaction;
                            $transaction->reference = 6;
                            $transaction->amount = ($fee_money_over_limit);
                            $transaction->received = ($fee_money_over_limit);
                            $transaction->type = 'DEPOSITAPP';
                            $transaction->type_money = 'VND';
                            $transaction->status = 1;
                            $transaction->user_id = $user_id;
                            $transaction->note = "Nạp tiền vào App";
                            $transaction->save();
    
                            $user = User::findOrFail(Auth::guard('api')->id());
                            $user->account_balance -= ($fee_money_over_limit);
                            $user->save();
    
                            $is_UserPackage->total_deposit_app += $request->amount;
                            $is_UserPackage->save();
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
                    if($is_UserPackage->total_deposit_app >= $package->max_deposit_app){
                        $fee_money_over_limit = ($request->amount/10000)*20;
                        // xử lý bình thường
                        $user = User::find(Auth::guard('api')->id());
                        if ($user->account_balance < ($fee_money_over_limit)) {
                            $res = [
                                'success' => false,
                                'data' => "Số dư không đủ để nạp vào App",
                            ];
                            return $res;
                        }
                        DB::beginTransaction();
                        try {
                            $user_bank_account = UserBankAccount::where('id',$request->user_bank_account_id)->first();
                            $account_balance = $request->amount + $user_bank_account->account_balance;
                            $user_bank_account->account_balance = $account_balance;
                            $user_bank_account->save();
    
                            // lưu vào lịch sử web
                            $user_id = Auth::guard('api')->id();
                            $transaction = new Transaction;
                            $transaction->reference = 6;
                            $transaction->amount = ($fee_money_over_limit);
                            $transaction->received = ($fee_money_over_limit);
                            $transaction->type = 'DEPOSITAPP';
                            $transaction->type_money = 'VND';
                            $transaction->status = 1;
                            $transaction->user_id = $user_id;
                            $transaction->note = "Nạp tiền vào App";
                            $transaction->save();
    
                            $user = User::findOrFail(Auth::guard('api')->id());
                            $user->account_balance -= ($fee_money_over_limit);
                            $user->save();
    
                            $is_UserPackage->total_deposit_app += $request->amount;
                            $is_UserPackage->save();
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
            }
        }else{
            $amount_deposit = $request->amount/100;
            // xử lý bình thường
            $user = User::find(Auth::guard('api')->id());
            if ($user->account_balance < ($amount_deposit)  ) {
                $res = [
                    'success' => false,
                    'data' => "Số dư không đủ để nạp vào App",
                ];
                return $res;
            }
            DB::beginTransaction();
            try {
                $user_bank_account = UserBankAccount::where('id',$request->user_bank_account_id)->first();
                $account_balance = $request->amount + $user_bank_account->account_balance;
                $user_bank_account->account_balance = $account_balance;
                $user_bank_account->save();

                // lưu vào lịch sử web
                $user_id = Auth::guard('api')->id();
                $transaction = new Transaction;
                $transaction->reference = 6;
                $transaction->amount = $amount_deposit;
                $transaction->received = $amount_deposit;
                $transaction->type = 'DEPOSITAPP';
                $transaction->type_money = 'VND';
                $transaction->status = 1;
                $transaction->user_id = $user_id;
                $transaction->note = "Nạp tiền vào App";
                $transaction->save();

                $user = User::findOrFail(Auth::guard('api')->id());
                $user->account_balance -= ($amount_deposit);
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
                \Log::error( $e->getMessage() );
                throw new Exception($e->getMessage());
            }
        }
        
    }

}