<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Auth;

class DeviceTokenController extends Controller
{
    public function save_device_token (Request $request) {
        try {
            $user = Auth::guard('api')->user();
            $user_account_bank = $user->active_bank_acount;
            if ($user_account_bank) {
                $object_user = json_decode($user_account_bank);
                $device_token = new DeviceToken;
                $device_token->device_token = $request->device_token;
                $device_token->user_id = $object_user->id;
                $device_token->save();
                $res = [
                    'success' => true,
                    'data' => $device_token,
                ];
                return $res;
            }else{
                $res = [
                    'success' => false,
                    'message' => 'Chưa đăng nhập!',
                ];
                return $res;
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
        
    }
}
