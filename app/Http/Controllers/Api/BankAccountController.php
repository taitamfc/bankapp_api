<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserBankAccountResource;
use App\Models\UserBankAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use DB;

class BankAccountController extends Controller
{
    public function getbankVietqr(Request $request)
    {
        $items = UserBankAccount::where('user_id', Auth::guard('api')->id())
            ->where('type', $request->type)
            ->get();
        $res = [
            'success' => true,
            'data' => $items,
        ];
        return $res;
    }

    public function checkAcountBank(Request $request)
    {
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
        DB::beginTransaction();
        try {
            if ($result['data'] != null) {
                $count_all_account = UserBankAccount::count();
                $count_check_acount_current = UserBankAccount::where('bank_number',$request->accountNumber)->count();
                if ($count_check_acount_current > 0) {
                    $res = [
                        'success' => false,
                        'data' => "Tài khoản đã tồn tại trong hệ thống!",
                    ];
                    return $res;
                }
                if ($count_all_account > 0) {
                    $user = User::find(Auth::guard('api')->id());
                    if ($user->account_balance >= 100000) {
                        $user_bank_account = new UserBankAccount;
                        $user_bank_account->name = $request->bank_name;
                        $user_bank_account->phone = $request->phone;
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
                    if ($user->account_balance >= 2000) {
                        $user_bank_account = new UserBankAccount;
                        $user_bank_account->name = $request->bank_name;
                        $user_bank_account->phone = $request->phone;
                        $user_bank_account->password_level_two = $request->password_level_two;
                        $user_bank_account->type = $request->type;
                        $user_bank_account->bank_number = $request->accountNumber;
                        $user_bank_account->user_id =  Auth::guard('api')->id();
                        $user_bank_account->bank_username = $result['data']['accountName'];
                        $user_bank_account->save();

                        $user->account_balance -= 2000;
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
                            'data' => "Số dư không đủ 2.000 đ!",
                        ];
                        return $res;
                    }
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
