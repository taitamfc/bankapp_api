<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Device;

class DeviceController extends Controller
{
    public function show (Request $request) {
        $deviceToken = sha1($request->header('User-Agent'));
        $device = Device::where('user_id',Auth::guard('api')->id())->where('deviceToken' , $deviceToken)->where('browser', $request->browserInfo)->first();
        if ($device) {
            $res = [
                'success' => true,
                'data' => $device,
            ];
            return $res;
        }else {
            $res = [
                'success' => false,
            ];
            return $res; 
        }
    }
}
