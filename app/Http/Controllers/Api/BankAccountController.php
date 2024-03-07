<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\UserBankAccountResource;
use App\Models\UserBankAccount;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class BankAccountController extends Controller
{
    public function getbankVietqr(Request $request)
    {
        $user = User::find(Auth::guard('api')->id());
        $items = UserBankAccount::where('user_id', Auth::guard('api')->id())
            ->where('type', $request->type)
            ->first();
        $items->setAttribute('phone', $user->phone);
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
        if ($result['data'] != null) {
            $user_bank_account = new UserBankAccount;
            $user_bank_account->name = $request->bank_name;
            $user_bank_account->type = $request->type;
            $user_bank_account->bank_number = $request->accountNumber;
            $user_bank_account->user_id =  Auth::guard('api')->id();
            $user_bank_account->bank_username = $result['data']['accountName'];
            $user_bank_account->save();
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
    }

}
