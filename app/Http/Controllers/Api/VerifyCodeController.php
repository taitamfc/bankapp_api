<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VerifyCode;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PasswordNotification;
use App\Notifications\SecondPassNotification;
use App\Notifications\EarnMoneyNotification;
use App\Notifications\PayMoneyNotification;
use App\Notifications\ChangeMailNotification;
use Illuminate\Support\Facades\Notification;

class VerifyCodeController extends Controller
{
    public function sendOTP(Request $request)
    {
        $user = User::where('id', Auth::guard('api')->id())->firstOrFail();  
        if ($request->type == "PASSWORD") {
            $code = mt_rand(100000, 999999);       
            $verify_code = new VerifyCode;
            $verify_code->type = 'PASSWORD';
            $verify_code->code = $code;
            $verify_code->user_id = Auth::guard('api')->id();
            $verify_code->save();
            $user->notify(new PasswordNotification($code));
            return response()->json([
                'success' => true,
                'data' => 'Mã xác nhận khôi phục mật khẩu đã được gửi vào Email của bạn!',
            ]);
        }

        if ($request->type == "SECONDPASS") {
            $code = mt_rand(100000, 999999);       
            $verify_code = new VerifyCode;
            $verify_code->type = 'SECONDPASS';
            $verify_code->code = $code;
            $verify_code->user_id = Auth::guard('api')->id();
            $verify_code->save();
            $user->notify(new SecondPassNotification($code));
            return response()->json([
                'success' => true,
                'data' => 'Mã xác nhận khôi phục mật khẩu cấp 2 đã được gửi vào Email của bạn!',
            ]);
        }
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
                'data' => 'Mã xác nhận đã được gửi vào Email của bạn!',
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
                'data' => 'Mã xác nhận chuyển tiền đã được gửi vào Email của bạn!',
            ]);
        }
        if ($request->type == "CHANGEMAIL") {
            if ($request->email) {
                $code = mt_rand(100000, 999999);       
                $verify_code = new VerifyCode;
                $verify_code->type = 'CHANGEMAIL';
                $verify_code->code = $code;
                $verify_code->email = $request->email;
                $verify_code->user_id = Auth::guard('api')->id();
                $verify_code->save();
                $user->notify(new ChangeMailNotification($code));
                return response()->json([
                    'success' => true,
                    'data' => 'Mã xác nhận chuyển tiền đã được gửi vào Email mới của bạn!',
                ]);
            }
        }
    }
}
