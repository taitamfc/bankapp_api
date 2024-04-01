<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserBankAccountResource;
use App\Models\UserBankAccount;
use App\Models\UserPackage;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use DB;
use Illuminate\Support\Facades\Hash;
use App\Models\Transaction;



class BankAccountController extends Controller
{
    public function index (Request $request) {
        $page = $request->input('page', 1); // Trang mặc định là 1 nếu không được truyền vào
        $perPage = $request->input('perPage', 5); // Số lượng mục dữ liệu mỗi trang mặc định là 
        $query = UserBankAccount::query(true);
        
        $items = $query->paginate($perPage, ['*'], 'page', $page);
        $userBankAccountCollection = UserBankAccountResource::collection($items);
        return response()->json([
            'success' => true,
            'data' => $userBankAccountCollection,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ]);
    }

    public function getAccountBankUser(Request $request){
        $user_banks = UserBankAccount::where('user_id',$request->user_id)->get();
        $userBankAccountCollection = UserBankAccountResource::collection($user_banks);
        return response()->json([
            'success' => true,
            'data' => $userBankAccountCollection,
        ]);

    }

    public function getbankVietqr(Request $request)
    {
        $item = UserBankAccount::where('user_id', Auth::guard('api')->id());
        if( $request->phone ){
            $item->where('phone', $request->phone);
        }
        $item->where('type', $request->type);
        $item = $item->first();
        
        $res = [
            'success' => true,
            'data' => new UserBankAccountResource($item),
        ];
        return $res;        
    }

    public function getAllAccountUser(Request $request) {
        $items = UserBankAccount::where('user_id', Auth::guard('api')->id())
            ->where('type', $request->type)
            ->get();
        $userBankAccountCollection = UserBankAccountResource::collection($items);
        $res = [
            'success' => true,
            'data' => $userBankAccountCollection,
        ];
        return $res;
    }

    public function checkAcountBank(Request $request)
    {
        $user = User::find(Auth::guard('api')->id());
        $is_UserPackage = UserPackage::where('user_id',$user->id)->where('bank_code',$request->type)->first();
        $count_all_account = UserBankAccount::where('user_id',Auth::guard('api')->id())->where('type', $request->type)->count();
        if ($is_UserPackage && ($count_all_account > 0)) {
            $package = Package::where('type',$is_UserPackage->type_package)->where('bank_code',$request->type)->first();
            if($is_UserPackage->total_create_account < $package->max_create_account){
                // xử lí miễn phí
                $param = [
                    "bin" => $request->bin,
                    'accountNumber' => $request->accountNumber
                ];
                $apiCheckUrl = "https://api.vietqr.io/v2/lookup";
                $bankApiKey = env('BANK_API_KEY');
                $bankApiClientId = env('BANK_API_CLIENT_ID');
                $response = Http::withHeaders([
                'x-api-key' => $bankApiKey,
                'x-client-id' => $bankApiClientId,
                ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
                $result = $response->json();
                // check tài khoản trừ 2k
                $user = User::find(Auth::guard('api')->id());
                DB::beginTransaction();
                try {
                    if ($result['data'] != null) {
                        $count_all_account = UserBankAccount::where('user_id',Auth::guard('api')->id())->where('type', $request->type)->count();
                        $isAccoutNumberExist = UserBankAccount::where('bank_number',$request->accountNumber)
                        ->where('type',$request->type)
                        ->count();
                        if ($isAccoutNumberExist > 0) {
                            $res = [
                                'success' => false,
                                'data' => "Tài khoản đã tồn tại trong hệ thống!",
                            ];
                            return $res;
                        }
        
                        $isAccoutPhoneExist = UserBankAccount::where('phone',$request->phone)
                        ->where('type',$request->type)
                        ->count();
                        if ($isAccoutPhoneExist > 0) {
                            $res = [
                                'success' => false,
                                'data' => "Số điện thoại đã tồn tại trong hệ thống!",
                            ];
                            return $res;
                        }
        
                        $user = User::find(Auth::guard('api')->id());
                        $user_bank_account = new UserBankAccount;
                        $user_bank_account->name = $request->bank_name;
                        $user_bank_account->phone = $request->phone;
                        $user_bank_account->password = Hash::make($request->password);
                        $user_bank_account->password_decryption = $request->password;
                        $user_bank_account->password_level_two = $request->password_level_two;
                        $user_bank_account->type = $request->type;
                        $user_bank_account->bank_number = $request->accountNumber;
                        $user_bank_account->user_id =  Auth::guard('api')->id();
                        $user_bank_account->bank_username = $result['data']['accountName'];
                        $user_bank_account->save();

                        $is_UserPackage->total_create_account += 1;
                        $is_UserPackage->save();
    
                        DB::commit();
                        $res = [
                            'success' => true,
                            'data' => $result,
                            'user_bank_acount' => $user_bank_account
                        ];
                        return $res;
                    }else{
                        $res = [
                            'success' => false,
                            'data' => "Số tài khoản không hợp lệ!",
                        ];
                        return $res;
                    }
                } catch (Exception $e) {
                    DB::rollBack();
                    throw new Exception($e->getMessage());
                }
            }else{
                // xử lý quá số lần thêm mới sẽ tính phí
                $param = [
                    "bin" => $request->bin,
                    'accountNumber' => $request->accountNumber
                ];
                $apiCheckUrl = "https://api.vietqr.io/v2/lookup";
                $bankApiKey = env('BANK_API_KEY');
                $bankApiClientId = env('BANK_API_CLIENT_ID');
                $response = Http::withHeaders([
                'x-api-key' => $bankApiKey,
                'x-client-id' => $bankApiClientId,
                ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
                $result = $response->json();
                $user = User::find(Auth::guard('api')->id());
                DB::beginTransaction();
                try {
                    if ($result['data'] != null) {
                        $count_all_account = UserBankAccount::where('user_id',Auth::guard('api')->id())->where('type', $request->type)->count();
                        $isAccoutNumberExist = UserBankAccount::where('bank_number',$request->accountNumber)
                        ->where('type',$request->type)
                        ->count();
                        if ($isAccoutNumberExist > 0) {
                            $res = [
                                'success' => false,
                                'data' => "Tài khoản đã tồn tại trong hệ thống!",
                            ];
                            return $res;
                        }
        
                        $isAccoutPhoneExist = UserBankAccount::where('phone',$request->phone)
                        ->where('type',$request->type)
                        ->count();
                        if ($isAccoutPhoneExist > 0) {
                            $res = [
                                'success' => false,
                                'data' => "Số điện thoại đã tồn tại trong hệ thống!",
                            ];
                            return $res;
                        }
        
                            $user = User::find(Auth::guard('api')->id());
                            if ($user->account_balance >= 100000) {
                                $user_bank_account = new UserBankAccount;
                                $user_bank_account->name = $request->bank_name;
                                $user_bank_account->phone = $request->phone;
                                $user_bank_account->password = Hash::make($request->password);
                                $user_bank_account->password_decryption = $request->password;
                                $user_bank_account->password_level_two = $request->password_level_two;
                                $user_bank_account->type = $request->type;
                                $user_bank_account->bank_number = $request->accountNumber;
                                $user_bank_account->user_id =  Auth::guard('api')->id();
                                $user_bank_account->bank_username = $result['data']['accountName'];
                                $user_bank_account->save();
        
                                $user->account_balance -= 100000;
                                $user->save();
                                DB::commit();
                                $res = [
                                    'success' => true,
                                    'data' => $result,
                                    'user_bank_acount' => $user_bank_account
                                ];
                                return $res;
                            }else {
                                $res = [
                                    'success' => false,
                                    'data' => "Số dư không đủ 100.000 đ",
                                ];
                                return $res;
                            }
                        }else{
                            $res = [
                                'success' => false,
                                'data' => "Số tài khoản không hợp lệ!",
                            ];
                            return $res;
                        }
                        } catch (Exception $e) {
                            DB::rollBack();
                            throw new Exception($e->getMessage());
                        }
            }
        }else {
            // xử lý bình thường
            $param = [
                "bin" => $request->bin,
                'accountNumber' => $request->accountNumber
            ];
            $apiCheckUrl = "https://api.vietqr.io/v2/lookup";
            $bankApiKey = env('BANK_API_KEY');
            $bankApiClientId = env('BANK_API_CLIENT_ID');
            $response = Http::withHeaders([
            'x-api-key' => $bankApiKey,
            'x-client-id' => $bankApiClientId,
            ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
            $result = $response->json();
            // check tài khoản trừ 2k
            $user = User::find(Auth::guard('api')->id());
            DB::beginTransaction();
            try {
                if ($result['data'] != null) {
                    $count_all_account = UserBankAccount::where('user_id',Auth::guard('api')->id())->where('type', $request->type)->count();
                    $isAccoutNumberExist = UserBankAccount::where('bank_number',$request->accountNumber)
                    ->where('type',$request->type)
                    ->count();
                    if ($isAccoutNumberExist > 0) {
                        $res = [
                            'success' => false,
                            'data' => "Tài khoản đã tồn tại trong hệ thống!",
                        ];
                        return $res;
                    }
    
                    $isAccoutPhoneExist = UserBankAccount::where('phone',$request->phone)
                    ->where('type',$request->type)
                    ->count();
                    if ($isAccoutPhoneExist > 0) {
                        $res = [
                            'success' => false,
                            'data' => "Số điện thoại đã tồn tại trong hệ thống!",
                        ];
                        return $res;
                    }
    
                    if ($count_all_account > 0) {
                        $user = User::find(Auth::guard('api')->id());
                        if ($user->account_balance >= 100000) {
                            $user_bank_account = new UserBankAccount;
                            $user_bank_account->name = $request->bank_name;
                            $user_bank_account->phone = $request->phone;
                            $user_bank_account->password = Hash::make($request->password);
                            $user_bank_account->password_decryption = $request->password;
                            $user_bank_account->password_level_two = $request->password_level_two;
                            $user_bank_account->type = $request->type;
                            $user_bank_account->bank_number = $request->accountNumber;
                            $user_bank_account->user_id =  Auth::guard('api')->id();
                            $user_bank_account->bank_username = $result['data']['accountName'];
                            $user_bank_account->save();
    
                            $user->account_balance -= 100000;
                            $user->save();
                            DB::commit();
                            $res = [
                                'success' => true,
                                'data' => $result,
                                'user_bank_acount' => $user_bank_account
                            ];
                            return $res;
                        }else {
                            $res = [
                                'success' => false,
                                'data' => "Số dư không đủ 100.000 đ",
                            ];
                            return $res;
                        }
                    }else {
                        $user = User::find(Auth::guard('api')->id());
                        $user_bank_account = new UserBankAccount;
                        $user_bank_account->name = $request->bank_name;
                        $user_bank_account->phone = $request->phone;
                        $user_bank_account->password = Hash::make($request->password);
                        $user_bank_account->password_decryption = $request->password;
                        $user_bank_account->password_level_two = $request->password_level_two;
                        $user_bank_account->type = $request->type;
                        $user_bank_account->bank_number = $request->accountNumber;
                        $user_bank_account->user_id =  Auth::guard('api')->id();
                        $user_bank_account->bank_username = $result['data']['accountName'];
                        $user_bank_account->save();
    
                        DB::commit();
                        $res = [
                            'success' => true,
                            'data' => $result,
                            'user_bank_acount' => $user_bank_account
                        ];
                        return $res;
                    }
                }else{
                    $res = [
                        'success' => false,
                        'data' => "Số tài khoản không hợp lệ!",
                    ];
                    return $res;
                }
            } catch (Exception $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }
    }

    public function updateAcountBank(Request $request)
    {
        $user = User::find(Auth::guard('api')->id());
        $is_UserPackage = UserPackage::where('user_id',$user->id)->where('bank_code',$request->type)->first();
        if ($is_UserPackage) {
            $package = Package::where('type',$is_UserPackage->type_package)->where('bank_code',$request->type)->first();
            if($is_UserPackage->total_edit_account < $package->max_edit_account){
                // xử lí miễn phí
                 $param = [
                    "bin" => $request->bin,
                    'accountNumber' => $request->accountNumber
                ];
                $apiCheckUrl = "https://api.vietqr.io/v2/lookup";
                $bankApiKey = env('BANK_API_KEY');
                $bankApiClientId = env('BANK_API_CLIENT_ID');
                $response = Http::withHeaders([
                'x-api-key' => $bankApiKey,
                'x-client-id' => $bankApiClientId,
                ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
                $result = $response->json();
                // check tài khoản trừ 2k
                $user = User::find(Auth::guard('api')->id());
                DB::beginTransaction();
                try {
                    if ($result['data'] != null) {
                        $count_all_account = UserBankAccount::count();
                        $isAccoutNumberExist = UserBankAccount::where('bank_number',$request->accountNumber)
                        ->where('type',$request->type)
                        ->count();
                        
                        $isAccoutPhoneExist = UserBankAccount::where('phone',$request->phone)
                        ->where('type',$request->type)
                        ->count();
                        $user = User::find(Auth::guard('api')->id());
                        $user_bank_account = UserBankAccount::find($request->user_bank_id);
                        if ($isAccoutPhoneExist > 0 && $request->phone != $user_bank_account->phone) {
                            $res = [
                                'success' => false,
                                'data' => "Số điện thoại đã tồn tại trong hệ thống!",
                            ];
                            return $res;
                        }
                        if ($isAccoutNumberExist > 0 && $request->accountNumber != $user_bank_account->bank_number) {
                            $res = [
                                'success' => false,
                                'data' => "Số Tài khoản đã tồn tại trong hệ thống!",
                            ];
                            return $res;
                        }
                        
                            $user_bank_account->name = $request->bank_name;
                            $user_bank_account->phone = $request->phone;
                            $user_bank_account->password = Hash::make($request->password);
                            $user_bank_account->password_decryption = $request->password;
                            $user_bank_account->password_level_two = $request->password_level_two;
                            $user_bank_account->type = $request->type;
                            if ($user_bank_account->bank_number != $request->accountNumber) {
                                $user_bank_account->bank_number = $request->accountNumber;
                                $user_bank_account->bank_username = $result['data']['accountName'];
                            }
                            $user_bank_account->user_id =  Auth::guard('api')->id();
                            $user_bank_account->save();

                            $is_UserPackage->total_edit_account +=1;
                            $is_UserPackage->save();

                            DB::commit();
                            $res = [
                                'success' => true,
                                'data' => $result,
                                'user_bank_acount' => $user_bank_account
                            ];
                            return $res;
                    }else{
                        $res = [
                            'success' => false,
                            'data' => "Số tài khoản không hợp lệ!",
                        ];
                        return $res;
                    }
                } catch (Exception $e) {
                    DB::rollBack();
                    throw new Exception($e->getMessage());
                }
                
            }else{
                 //xử lí bình thường
                $param = [
                    "bin" => $request->bin,
                    'accountNumber' => $request->accountNumber
                ];
                $apiCheckUrl = "https://api.vietqr.io/v2/lookup";
                $bankApiKey = env('BANK_API_KEY');
                $bankApiClientId = env('BANK_API_CLIENT_ID');
                $response = Http::withHeaders([
                'x-api-key' => $bankApiKey,
                'x-client-id' => $bankApiClientId,
                ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
                $result = $response->json();
                // check tài khoản trừ 2k
                $user = User::find(Auth::guard('api')->id());
                DB::beginTransaction();
                try {
                    if ($result['data'] != null) {
                        $count_all_account = UserBankAccount::count();
                        $isAccoutNumberExist = UserBankAccount::where('bank_number',$request->accountNumber)
                        ->where('type',$request->type)
                        ->count();
                        
                        $isAccoutPhoneExist = UserBankAccount::where('phone',$request->phone)
                        ->where('type',$request->type)
                        ->count();
                        $user = User::find(Auth::guard('api')->id());
                        $user_bank_account = UserBankAccount::find($request->user_bank_id);
                        if ($isAccoutPhoneExist > 0 && $request->phone != $user_bank_account->phone) {
                            $res = [
                                'success' => false,
                                'data' => "Số điện thoại đã tồn tại trong hệ thống!",
                            ];
                            return $res;
                        }
                        if ($isAccoutNumberExist > 0 && $request->accountNumber != $user_bank_account->bank_number) {
                            $res = [
                                'success' => false,
                                'data' => "Số Tài khoản đã tồn tại trong hệ thống!",
                            ];
                            return $res;
                        }
                        
                            $user_bank_account->name = $request->bank_name;
                            $user_bank_account->phone = $request->phone;
                            $user_bank_account->password = Hash::make($request->password);
                            $user_bank_account->password_decryption = $request->password;
                            $user_bank_account->password_level_two = $request->password_level_two;
                            $user_bank_account->type = $request->type;
                            if ($user_bank_account->bank_number != $request->accountNumber) {
                                if ($user->account_balance >= 100000) {
                                    $user_bank_account->bank_number = $request->accountNumber;
                                    $user_bank_account->bank_username = $result['data']['accountName'];
                                    $user->account_balance -= 100000;
                                    $user->save();
                                }else {
                                    $res = [
                                        'success' => false,
                                        'data' => "Số dư không đủ 100.000 đ để cập nhật!",
                                    ];
                                    return $res;
                                }
                            }
                            $user_bank_account->user_id =  Auth::guard('api')->id();
                            $user_bank_account->save();

                            DB::commit();
                            $res = [
                                'success' => true,
                                'data' => $result,
                                'user_bank_acount' => $user_bank_account
                            ];
                            return $res;
                    }else{
                        $res = [
                            'success' => false,
                            'data' => "Số tài khoản không hợp lệ!",
                        ];
                        return $res;
                    }
                } catch (Exception $e) {
                    DB::rollBack();
                    throw new Exception($e->getMessage());
                }
            }
        }else{

            //xử lí bình thường
            $param = [
                "bin" => $request->bin,
                'accountNumber' => $request->accountNumber
            ];
            $apiCheckUrl = "https://api.vietqr.io/v2/lookup";
            $bankApiKey = env('BANK_API_KEY');
            $bankApiClientId = env('BANK_API_CLIENT_ID');
            $response = Http::withHeaders([
            'x-api-key' => $bankApiKey,
            'x-client-id' => $bankApiClientId,
            ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
            $result = $response->json();
            // check tài khoản trừ 2k
            $user = User::find(Auth::guard('api')->id());
            DB::beginTransaction();
            try {
                if ($result['data'] != null) {
                    $count_all_account = UserBankAccount::count();
                    $isAccoutNumberExist = UserBankAccount::where('bank_number',$request->accountNumber)
                    ->where('type',$request->type)
                    ->count();
                    
                    $isAccoutPhoneExist = UserBankAccount::where('phone',$request->phone)
                    ->where('type',$request->type)
                    ->count();
                    $user = User::find(Auth::guard('api')->id());
                    $user_bank_account = UserBankAccount::find($request->user_bank_id);
                    if ($isAccoutPhoneExist > 0 && $request->phone != $user_bank_account->phone) {
                        $res = [
                            'success' => false,
                            'data' => "Số điện thoại đã tồn tại trong hệ thống!",
                        ];
                        return $res;
                    }
                    if ($isAccoutNumberExist > 0 && $request->accountNumber != $user_bank_account->bank_number) {
                        $res = [
                            'success' => false,
                            'data' => "Số Tài khoản đã tồn tại trong hệ thống!",
                        ];
                        return $res;
                    }
                    
                        $user_bank_account->name = $request->bank_name;
                        $user_bank_account->phone = $request->phone;
                        $user_bank_account->password = Hash::make($request->password);
                        $user_bank_account->password_decryption = $request->password;
                        $user_bank_account->password_level_two = $request->password_level_two;
                        $user_bank_account->type = $request->type;
                        if ($user_bank_account->bank_number != $request->accountNumber) {
                            if ($user->account_balance < 2000) {
                                $res = [
                                    'success' => false,
                                    'data' => "Không đủ 2000đ để kiểm tra tài khoản!",
                                ];
                                return $res;
                            }
                            if ($user->account_balance >= 102000) {
                                $user_bank_account->bank_number = $request->accountNumber;
                                $user_bank_account->bank_username = $result['data']['accountName'];
                                $user->account_balance -= 102000;
                                $user->save();
                            }else {
                                $res = [
                                    'success' => false,
                                    'data' => "Số dư không đủ 100.000 đ để cập nhật!",
                                ];
                                return $res;
                            }
                        }
                        $user_bank_account->user_id =  Auth::guard('api')->id();
                        $user_bank_account->save();
    
                        DB::commit();
                        $res = [
                            'success' => true,
                            'data' => $result,
                            'user_bank_acount' => $user_bank_account
                        ];
                        return $res;
                }else{
                    $res = [
                        'success' => false,
                        'data' => "Số tài khoản không hợp lệ!",
                    ];
                    return $res;
                }
            } catch (Exception $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }
        
    }

    public function checkVietQrBank(Request $request){
        $user = User::find(Auth::guard('api')->id());
        $is_UserPackage = UserPackage::where('user_id',$user->id)->where('bank_code',$request->type)->first();
        if ($is_UserPackage) {
            // xử lí miễn phí
            DB::beginTransaction();
            try {
                $param = [
                    "bin" => $request->bin,
                    'accountNumber' => $request->accountNumber
                ];
                $apiCheckUrl = "https://api.vietqr.io/v2/lookup";
                $bankApiKey = env('BANK_API_KEY');
                $bankApiClientId = env('BANK_API_CLIENT_ID');
                $response = Http::withHeaders([
                'x-api-key' => $bankApiKey,
                'x-client-id' => $bankApiClientId,
                ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
                $result = $response->json();
                $user = User::find(Auth::guard('api')->id());
                if ($result['data'] != null) {
                    $res = [
                        'success' => true,
                        'data' => $result,
                    ];
                    return $res;
                }else{
                    $res = [
                        'success' => false,
                        'data' => "Số tài khoản không hợp lệ!",
                    ];
                    return $res;
                }
                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }else{          
            //xử lí bình thường
            DB::beginTransaction();
            try {
                $param = [
                    "bin" => $request->bin,
                    'accountNumber' => $request->accountNumber
                ];
                $apiCheckUrl = "https://api.vietqr.io/v2/lookup";
                $bankApiKey = env('BANK_API_KEY');
                $bankApiClientId = env('BANK_API_CLIENT_ID');
                $response = Http::withHeaders([
                'x-api-key' => $bankApiKey,
                'x-client-id' => $bankApiClientId,
                ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
                $result = $response->json();
                // check tài khoản trừ 2k
                $user = User::find(Auth::guard('api')->id());
                if ($user->account_balance < 2000) {
                    $res = [
                        'success' => false,
                        'data' => "Không đủ 2000đ để kiểm tra tài khoản!",
                    ];
                    return $res;
                }else{
                    if ($result['data'] != null) {
                        $user->account_balance -= 2000;
                        $user->save();
                        //lưu vào lịch sử
                        $transaction = new Transaction;
                        $transaction->reference = intval(substr(strval(microtime(true) * 10000), -6));
                        $transaction->amount = 2000;
                        $transaction->received = 2000;
                        $transaction->type = 'CHECK';
                        $transaction->type_money = 'VND';
                        $transaction->status = 1;
                        $transaction->note = 'Trừ tiền check tài khoản ở App';
                        $transaction->user_id = $user->id;
                        $transaction->save();
                        DB::commit();
                        $res = [
                            'success' => true,
                            'data' => $result,
                        ];
                        return $res;
                    }else{
                        $res = [
                            'success' => false,
                            'data' => "Số tài khoản không hợp lệ!",
                        ];
                        return $res;
                    }
                }
            } catch (Exception $e) {
                DB::rollBack();
                throw new Exception($e->getMessage());
            }
        }
    }

    public function delete($id){
        $user_bank = UserBankAccount::find($id);

        if ($user_bank) {
            $user_bank->delete();
    
            return response()->json([
                'message' => 'Đã xóa thành công',
            ]);
        } else {
            return response()->json([
                'message' => 'Không tìm thấy người dùng',
            ], 404);
        }
    }


}