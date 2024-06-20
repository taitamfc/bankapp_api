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
use Illuminate\Support\Facades\Http;
use App\Models\DeviceToken;


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
                $countTransferToday = TransactionApp::whereDate('created_at', $today)->where('type', 'TRANSFER')->where('from_number', $user_bank_account->bank_number)->where('created_at', '>' ,$is_UserPackage->created_at)->count();
                $package = Package::where('type',$is_UserPackage->type_package)->where('bank_code',$request->type)->first();
                if ($package->max_transfer_free == -1) {
                    // xử lí miễn phí
                    $user = User::find(Auth::guard('api')->id());
                    DB::beginTransaction();
                    try {
                        $data = $request->except('_method','_token');
                        if ($data['amount'] > 1000000000) {
                            $res = [
                                'success' => false,
                                'error' => "Số tiền chuyển khoản quá 1 tỷ đồng!",
                            ];
                            return $res;
                        }
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


                        $data_api_all_bank_vietQR = CheckVietQR::DATA_BANK_VIETQR;
                        foreach ($data_api_all_bank_vietQR as $key => $value) {
                            if ($data['type'] == "VCB" && $value['short_name'] == $data['bank_code_id']) {
                                $value['short_name'] = strtoupper($value['short_name']);
                                $name_bank = $value['name']."(".$value['short_name'].")";
                                $code_bank = $value['code'];
                            }elseif ($value['short_name'] == $data['bank_code_id']) {
                                $name_bank = $value['name'];
                                $code_bank = $value['code'];
                            }
                        }
                        if ($data['bank_code_id'] == "MB" || $data['bank_code_id'] == "TCB") {
                            foreach ($data_api_all_bank_vietQR as $key => $value) {
                                if ($value['code'] == $data['bank_code_id']) {
                                    $name_bank = $value['name'];
                                }
                            }
                        }
                        $data['bank_name'] = $name_bank;
                        if ($user_current->type == "VCB") {
                            $randomNumberVCB = mt_rand(100000000, 999999999);
                            $data['transaction_code'] = "5".$randomNumberVCB; // tự động random
                        }elseif ($user_current->type == "TCB"){
                            $randomNumber = mt_rand(100000000000, 999999999999);
                            $data['transaction_code'] = "FT24".$randomNumber; // tự động random
                        }elseif ($user_current->type == "BIDV"){
                            $randomNumber = mt_rand(100000, 999999);
                            $randomNumber = (string) $randomNumber;
                            $data['transaction_code'] = $randomNumber; // tự động random
                        }elseif ($user_current->type == "MB"){
                            $randomNumber = mt_rand(100000000000, 999999999999);
                            $data['transaction_code'] = "FT24".$randomNumber; // tự động random
                        }elseif ($user_current->type == "ICB"){
                            $string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                            $randomChars = substr(str_shuffle($string), 0, 8);
                            $data['transaction_code'] = "932S22A1".$randomChars; // tự động random
                        }
                        $data['user_bank_account_id'] = $user_current->id;
                        $data['from_name'] = $user_current->bank_username;
                        $data['from_number'] = $user_current->bank_number;
                        $data['account_balance'] = $user_current->account_balance;
                        $data['type'] = "TRANSFER";
                        $data['received_amount'] = $data['amount'];
                        $data['fee_amount'] = 0;
                        $item = TransactionApp::create($data);

                        $user_acount_recipient = UserBankAccount::where('type',$code_bank)->where('bank_number',$data['recipient_account_number'])->first();
                        if ($user_acount_recipient != null) {
                            $user_acount_recipient->account_balance += $data['amount'];
                            $user_acount_recipient->save();

                        //     // lưu vào lịch sử người nhận
                            $transaction_app = new TransactionApp;
                            $transaction_app->reference = intval(substr(strval(microtime(true) * 10000), -6));
                            $transaction_app->from_name = $user_current->bank_username;
                            $transaction_app->recipient_name = $data['recipient_name'];
                            $transaction_app->bank_name = $name_bank;
                            $transaction_app->from_number = $user_current->bank_number;
                            $transaction_app->recipient_account_number = $data['recipient_account_number'];
                            $transaction_app->type = 'APPTRANSFERAPP';
                            $randomNumber = mt_rand(100000000000, 999999999999);
                            $transaction_app->transaction_code = "FT23".$randomNumber;
                            $transaction_app->bank_code_id = $data['bank_code_id'];
                            $transaction_app->amount = $data['amount'];
                            $transaction_app->received_amount = $data['amount'];
                            $transaction_app->account_balance = $user_acount_recipient->account_balance;
                            $transaction_app->note = $data['note'];
                            $transaction_app->user_bank_account_id = $user_acount_recipient->id;


                            $transaction_app->save();

                            $device_token = DeviceToken::where('user_id',$user_acount_recipient->id)->first();
                            if ($device_token) {
                                $transaction_app->device_token = $device_token->device_token;
                            }else{
                                $transaction_app->device_token = null;
                            }

                            $notification_mess = $this->custom_noti_bank($user_acount_recipient->type,$transaction_app);

                            Http::post(config('api.api_url_notification'), [
                                'bank_number' => $user_acount_recipient->bank_number,
                                'title' => $notification_mess['title'],
                                'body' => $notification_mess['body'],
                            ]);
                        }

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
                            if ($data['amount'] > 1000000000) {
                                $res = [
                                    'success' => false,
                                    'error' => "Số tiền chuyển khoản quá 1 tỷ đồng!",
                                ];
                                return $res;
                            }
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
                            $data_api_all_bank_vietQR = CheckVietQR::DATA_BANK_VIETQR;
                            foreach ($data_api_all_bank_vietQR as $key => $value) {
                                if ($data['type'] == "VCB" && $value['short_name'] == $data['bank_code_id']) {
                                    $value['short_name'] = strtoupper($value['short_name']);
                                    $name_bank = $value['name']."(".$value['short_name'].")";
                                    $code_bank = $value['code'];
                                }elseif ($value['short_name'] == $data['bank_code_id']) {
                                    $name_bank = $value['name'];
                                    $code_bank = $value['code'];
                                }
                            }
                            if ($data['bank_code_id'] == "MB" || $data['bank_code_id'] == "TCB") {
                                foreach ($data_api_all_bank_vietQR as $key => $value) {
                                    if ($value['code'] == $data['bank_code_id']) {
                                        $name_bank = $value['name'];
                                    }
                                }
                            }
                            $data['bank_name'] = $name_bank;
                            if ($user_current->type == "VCB") {
                                $randomNumberVCB = mt_rand(100000000, 999999999);
                                $data['transaction_code'] = "5".$randomNumberVCB; // tự động random
                            }elseif ($user_current->type == "TCB"){
                                $randomNumber = mt_rand(100000000000, 999999999999);
                                $data['transaction_code'] = "FT24".$randomNumber; // tự động random
                            }elseif ($user_current->type == "BIDV"){
                                $randomNumber = mt_rand(100000, 999999);
                                $randomNumber = (string) $randomNumber;
                                $data['transaction_code'] = $randomNumber; // tự động random
                            }elseif ($user_current->type == "MB"){
                                $randomNumber = mt_rand(100000000000, 999999999999);
                                $data['transaction_code'] = "FT24".$randomNumber; // tự động random
                            }elseif ($user_current->type == "ICB"){
                                $string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                                $randomChars = substr(str_shuffle($string), 0, 8);
                                $data['transaction_code'] = "932S22A1".$randomChars; // tự động random
                            }
                            $data['user_bank_account_id'] = $user_current->id;
                            $data['from_name'] = $user_current->bank_username;
                            $data['from_number'] = $user_current->bank_number;
                            $data['account_balance'] = $user_current->account_balance;
                            $data['type'] = "TRANSFER";
                            $data['received_amount'] = $data['amount'];
                            $data['fee_amount'] = 0;
                            $item = TransactionApp::create($data);

                            $user_acount_recipient = UserBankAccount::where('type',$code_bank)->where('bank_number',$data['recipient_account_number'])->first();
                            if ($user_acount_recipient != null) {
                                $user_acount_recipient->account_balance += $data['amount'];
                                $user_acount_recipient->save();

                            //     // lưu vào lịch sử người nhận
                                $transaction_app = new TransactionApp;
                                $transaction_app->reference = intval(substr(strval(microtime(true) * 10000), -6));
                                $transaction_app->from_name = $user_current->bank_username;
                                $transaction_app->recipient_name = $data['recipient_name'];
                                $transaction_app->bank_name = $name_bank;
                                $transaction_app->from_number = $user_current->bank_number;
                                $transaction_app->recipient_account_number = $data['recipient_account_number'];
                                $transaction_app->type = 'APPTRANSFERAPP';
                                $randomNumber = mt_rand(100000000000, 999999999999);
                                $transaction_app->transaction_code = "FT23".$randomNumber;
                                $transaction_app->bank_code_id = $data['bank_code_id'];
                                $transaction_app->amount = $data['amount'];
                                $transaction_app->received_amount = $data['amount'];
                                $transaction_app->account_balance = $user_acount_recipient->account_balance;
                                $transaction_app->note = $data['note'];
                                $transaction_app->user_bank_account_id = $user_acount_recipient->id;


                                $transaction_app->save();

                                $device_token = DeviceToken::where('user_id',$user_acount_recipient->id)->first();
                                if ($device_token) {
                                    $transaction_app->device_token = $device_token->device_token;
                                }else{
                                    $transaction_app->device_token = null;
                                }

                                $notification_mess = $this->custom_noti_bank($user_acount_recipient->type,$transaction_app);

                                Http::post(config('api.api_url_notification'), [
                                    'bank_number' => $user_acount_recipient->bank_number,
                                    'title' => $notification_mess['title'],
                                    'body' => $notification_mess['body'],
                                ]);
                            }
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
                            if ($data['amount'] > 1000000000) {
                                $res = [
                                    'success' => false,
                                    'error' => "Số tiền chuyển khoản quá 1 tỷ đồng!",
                                ];
                                return $res;
                            }
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
                            $data_api_all_bank_vietQR = CheckVietQR::DATA_BANK_VIETQR;
                            foreach ($data_api_all_bank_vietQR as $key => $value) {
                                if ($data['type'] == "VCB" && $value['short_name'] == $data['bank_code_id']) {
                                    $value['short_name'] = strtoupper($value['short_name']);
                                    $name_bank = $value['name']."(".$value['short_name'].")";
                                    $code_bank = $value['code'];
                                }elseif ($value['short_name'] == $data['bank_code_id']) {
                                    $name_bank = $value['name'];
                                    $code_bank = $value['code'];
                                }
                            }
                            if ($data['bank_code_id'] == "MB" || $data['bank_code_id'] == "TCB") {
                                foreach ($data_api_all_bank_vietQR as $key => $value) {
                                    if ($value['code'] == $data['bank_code_id']) {
                                        $name_bank = $value['name'];
                                    }
                                }
                            }
                            if ($data['bank_code_id'] == "MB" || $data['bank_code_id'] == "TCB") {
                                foreach ($data_api_all_bank_vietQR as $key => $value) {
                                    if ($value['code'] == $data['bank_code_id']) {
                                        $name_bank = $value['name'];
                                    }
                                }
                            }
                            $data['bank_name'] = $name_bank;
                            if ($user_current->type == "VCB") {
                                $randomNumberVCB = mt_rand(100000000, 999999999);
                                $data['transaction_code'] = "5".$randomNumberVCB; // tự động random
                            }elseif ($user_current->type == "TCB"){
                                $randomNumber = mt_rand(100000000000, 999999999999);
                                $data['transaction_code'] = "FT24".$randomNumber; // tự động random
                            }elseif ($user_current->type == "BIDV"){
                                $randomNumber = mt_rand(100000, 999999);
                                $randomNumber = (string) $randomNumber;
                                $data['transaction_code'] = $randomNumber; // tự động random
                            }elseif ($user_current->type == "MB"){
                                $randomNumber = mt_rand(100000000000, 999999999999);
                                $data['transaction_code'] = "FT24".$randomNumber; // tự động random
                            }elseif ($user_current->type == "ICB"){
                                $string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                                $randomChars = substr(str_shuffle($string), 0, 8);
                                $data['transaction_code'] = "932S22A1".$randomChars; // tự động random
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

                            // xử lý cộng tiền cho người nhận nội bộ nếu có
                            $user_acount_recipient = UserBankAccount::where('type',$code_bank)->where('bank_number',$data['recipient_account_number'])->first();
                            if ($user_acount_recipient != null) {
                                $user_acount_recipient->account_balance += $data['amount'];
                                $user_acount_recipient->save();

                                // lưu vào lịch sử người nhận
                                $transaction_app = new TransactionApp;
                                $transaction_app->reference = intval(substr(strval(microtime(true) * 10000), -6));
                                $transaction_app->from_name = $user_current->bank_username;
                                $transaction_app->recipient_name = $data['recipient_name'];
                                $transaction_app->bank_name = $name_bank;
                                $transaction_app->from_number = $user_current->bank_number;
                                $transaction_app->recipient_account_number = $data['recipient_account_number'];
                                $transaction_app->type = 'APPTRANSFERAPP';
                                $randomNumber = mt_rand(100000000000, 999999999999);
                                $transaction_app->transaction_code = "FT23".$randomNumber;
                                $transaction_app->bank_code_id = $data['bank_code_id'];
                                $transaction_app->amount = $data['amount'];
                                $transaction_app->received_amount = $data['amount'];
                                $transaction_app->account_balance = $user_acount_recipient->account_balance;
                                $transaction_app->note = $data['note'];
                                $transaction_app->user_bank_account_id = $user_acount_recipient->id;

                                $transaction_app->save();

                                $device_token = DeviceToken::where('user_id',$user_acount_recipient->id)->first();
                                if ($device_token) {
                                    $transaction_app->device_token = $device_token->device_token;
                                }else{
                                    $transaction_app->device_token = null;
                                }

                                $notification_mess = $this->custom_noti_bank($user_acount_recipient->type,$transaction_app);

                                Http::post(config('api.api_url_notification'), [
                                    'bank_number' => $user_acount_recipient->bank_number,
                                    'title' => $notification_mess['title'],
                                    'body' => $notification_mess['body'],
                                ]);
                            }

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
                    if ($data['amount'] > 1000000000) {
                        $res = [
                            'success' => false,
                            'error' => "Số tiền chuyển khoản quá 1 tỷ đồng!",
                        ];
                        return $res;
                    }
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
                    
                    $data_api_all_bank_vietQR = CheckVietQR::DATA_BANK_VIETQR;
                    foreach ($data_api_all_bank_vietQR as $key => $value) {
                        if ($data['type'] == "VCB" && $value['short_name'] == $data['bank_code_id']) {
                            $value['short_name'] = strtoupper($value['short_name']);
                            $name_bank = $value['name']."(".$value['short_name'].")";
                            $code_bank = $value['code'];
                        }elseif ($value['short_name'] == $data['bank_code_id']) {
                            $name_bank = $value['name'];
                            $code_bank = $value['code'];
                        }
                    }
                    if ($data['bank_code_id'] == "MB" || $data['bank_code_id'] == "TCB") {
                        foreach ($data_api_all_bank_vietQR as $key => $value) {
                            if ($value['code'] == $data['bank_code_id']) {
                                $name_bank = $value['name'];
                            }
                        }
                    }
                    $data['bank_name'] = $name_bank;

                    if ($user_current->type == "VCB") {
                        $randomNumberVCB = mt_rand(100000000, 999999999);
                        $data['transaction_code'] = "5".$randomNumberVCB; // tự động random
                    }elseif ($user_current->type == "TCB"){
                        $randomNumber = mt_rand(100000000000, 999999999999);
                        $data['transaction_code'] = "FT24".$randomNumber; // tự động random
                    }elseif ($user_current->type == "BIDV"){
                        $randomNumber = mt_rand(100000, 999999);
                        $randomNumber = (string) $randomNumber;
                        $data['transaction_code'] = $randomNumber; // tự động random
                    }elseif ($user_current->type == "MB"){
                        $randomNumber = mt_rand(100000000000, 999999999999);
                        $data['transaction_code'] = "FT24".$randomNumber; // tự động random
                    }elseif ($user_current->type == "ICB"){
                        $string = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                        $randomChars = substr(str_shuffle($string), 0, 8);
                        $data['transaction_code'] = "932S22A1".$randomChars; // tự động random
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


                    // xử lý cộng tiền cho người nhận nội bộ nếu có
                    $user_acount_recipient = UserBankAccount::where('type',$code_bank)->where('bank_number',$data['recipient_account_number'])->first();
                    if ($user_acount_recipient != null) {
                        $user_acount_recipient->account_balance += $data['amount'];
                        $user_acount_recipient->save();

                        // lưu vào lịch sử người nhận
                        $transaction_app = new TransactionApp;
                        $transaction_app->reference = intval(substr(strval(microtime(true) * 10000), -6));
                        $transaction_app->from_name = $user_current->bank_username;
                        $transaction_app->recipient_name = $data['recipient_name'];
                        $transaction_app->bank_name = $name_bank;
                        $transaction_app->from_number = $user_current->bank_number;
                        $transaction_app->recipient_account_number = $data['recipient_account_number'];
                        $transaction_app->type = 'APPTRANSFERAPP';
                        $randomNumber = mt_rand(100000000000, 999999999999);
                        $transaction_app->transaction_code = "FT23".$randomNumber;
                        $transaction_app->bank_code_id = $data['bank_code_id'];
                        $transaction_app->amount = $data['amount'];
                        $transaction_app->received_amount = $data['amount'];
                        $transaction_app->account_balance = $user_acount_recipient->account_balance;
                        $transaction_app->user_bank_account_id = $user_acount_recipient->id;
                        $transaction_app->note = $data['note'];

                        $transaction_app->save();

                        $device_token = DeviceToken::where('user_id',$user_acount_recipient->id)->first();
                        if ($device_token) {
                            $transaction_app->device_token = $device_token->device_token;
                        }else{
                            $transaction_app->device_token = null;
                        }

                        $notification_mess = $this->custom_noti_bank($user_acount_recipient->type,$transaction_app);

                        Http::post(config('api.api_url_notification'), [
                            'bank_number' => $user_acount_recipient->bank_number,
                            'title' => $notification_mess['title'],
                            'body' => $notification_mess['body'],
                        ]);
                    }
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

    private function custom_noti_bank($type,$data) {
        switch ($type) {
            case 'TCB':
                return $this->notification_tcb($data);
                break;
            case 'VCB':
                return $this->notification_vcb($data);
                break;
            case 'MB':
                return $this->notification_mb($data);
                break;
            case 'BIDV':
                return $this->notification_bidv($data);
                break;
            default:
                return $this->notification_icb($data);
                break;
        }
    }

    private function notification_tcb($data) {
        $res = [
            'title' => '+ VND '.number_format($data->received_amount),
            'body' => 'Tài khoản: '.$data->recipient_account_number. chr(10) .'Số dư: VND '.number_format($data->account_balance). ' VNĐ'. chr(10) .$data->note ,
        ];
        return $res;
    }

    private function notification_vcb($data) {
        $res = [
            'title' => 'Thông báo VCB',
            'body' => 'Số dư TK VCB '.$data->recipient_account_number.' +'.number_format($data->received_amount). ' VND lúc '.$data->created_at.'. Số dư '.number_format($data->account_balance).' VND. Ref '.$data->transaction_code.'.'.$data->note,
        ];
        return $res;
    }

    private function notification_mb($data) {
        $formatted_date = date("d/m/Y H:i", strtotime($data->created_at));
        $res = [
            'title' => 'Thông báo biến động số dư',
            'body' => 'TK: '.$data->recipient_account_number.'|GD: +'.number_format($data->received_amount).'VND '.$formatted_date.' |SD: '.number_format($data->account_balance).'VND|TU: '.$data->from_name.' - '.$data->from_number,
        ];
        return $res;
    }
    private function notification_bidv($data) {
        $formatted_date = date("H:i d/m/Y", strtotime($data->created_at));
        $res = [
            'title' => 'Thông báo BIDV',
            'body' => $formatted_date.' Tài khoản thanh toán '.$data->recipient_account_number.'. Số tiền: +'.number_format($data->received_amount).'VND. Số dư cuối: '.number_format($data->account_balance).'VND Nội dung giao dịch: TKThe :'.$data->from_number,
        ];
        return $res;
    }
    private function notification_icb($data) {
        $formatted_date = date("d/m/Y H:i", strtotime($data->created_at));
        $res = [
            'title' => 'Biến động số dư',
            'body' => 'Thời gian: '.$formatted_date. chr(10) .
                        'Tài khoản: '.$data->recipient_account_number.chr(10).
                        'Giao dịch: +'.number_format($data->received_amount).' VND'.chr(10).
                        'Số dư hiện tại: '.number_format($data->account_balance).' VND.',
        ];
        return $res;
    }

    public function handleACBReturn(Request $request)
    {
        $accessToken = config('acb_config.spayment_access_token');
        $params = $request->all();

        Log::info(json_encode($params));
        Log::info("TOKEN: " . $accessToken);
    }
}
