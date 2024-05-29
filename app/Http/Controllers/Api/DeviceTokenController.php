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
            $device_token = new DeviceToken;
            $device_token->device_token = $request->device_token;
            $device_token->user_id = Auth::guard('api')->id();
            $device_token->save();
            $res = [
                'success' => true,
                'data' => $device_token,
            ];
            return $res;
        } catch (\Throwable $th) {
            //throw $th;
        }
        
    }
}
