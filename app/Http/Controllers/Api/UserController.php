<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\SecondPasswordRequest;
use App\Notifications\PasswordNotification;
use App\Notifications\SecondPassNotification;
use App\Models\VerifyCode;
use App\Http\Requests\OtpPasswordRequest;
use Illuminate\Support\Str;


class UserController extends Controller
{
    public function show()
    {
        $user_id = Auth::guard('api')->id();
        $data = new UserResource(User::findOrFail($user_id));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    public function update(Request $request)
    {
        $user_id = Auth::guard('api')->id();
        $user = User::findOrFail($user_id);
        $user->name = $request->name;
        $user->user_name = $request->user_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hồ sơ thành công!',
            'data' => $user
        ]);
    }

    public function changepassword(SecondPasswordRequest $request)
    {
        $user = User::findOrFail(Auth::guard('api')->id());
        if ($request->type == "PASSWORD") {
                if (Hash::check($request->old_password, $user->password)) {
                    $user->password = Hash::make($request->new_password);
                    $user->save();
                    return response()->json([
                        'success' => true,
                        'message' => 'Cập Nhật Mật Khẩu Thành Công!',
                        'data' => $user
                    ]);
                }else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Thất bại, mật khẩu cũ không đúng!',
                    ]);
                }
        }
        if ($request->type == "SECONDPASS") {
                if (Hash::check($request->old_password, $user->password_confirmation)) {
                    $user->password_confirmation = Hash::make($request->new_password);
                    $user->save();
                    return response()->json([
                        'success' => true,
                        'message' => 'Cập Nhật Mật Khẩu Cấp 2 Thành Công!',
                        'data' => $user
                    ]);
                }else {
                    return response()->json([
                        'success' => false,
                        'data' => 'Thất bại, mật khẩu cũ không đúng!',
                    ]);
                }
        }
    }

    public function restorePassword(OtpPasswordRequest $request)
    {
        $user_id = Auth::guard('api')->id();
        if ($request->type == "PASSWORD") {
            $verify_code = VerifyCode::where('user_id', $user_id)
                            ->where('type', 'PASSWORD')
                            ->orderBy('id', 'desc')
                            ->first();
            if($verify_code == null){
                $res = [
                    'success' => false,
                    'data' => 'Vui lòng lấy mã xác nhận trước khi thực hiện khôi phục!',
                ];
                return response()->json($res);
            }                
            $code = $verify_code->code;
            if ($request->verify_code == $code) {
                $user = User::findOrFail($user_id);
                $newPassword = Str::random(6);
                $user->password = Hash::make($newPassword);
                $user->save();
                $res = [
                    'success' => true,
                    'data' => 'Mật khẩu mới của bạn là: '.$newPassword,
                    'newPassword' => $newPassword,
                ];
                return response()->json($res, 200);
            }else {
                $res = [
                    'success' => false,
                    'data' => 'Mã xác nhận sai, vui lòng kiểm tra lại!',
                ];
                return response()->json($res);
            }
        }

        if ($request->type == "SECONDPASS") {
            $verify_code = VerifyCode::where('user_id', $user_id)
                        ->where('type', 'SECONDPASS')
                        ->orderBy('id', 'desc')
                        ->first();
            if($verify_code == null){
                $res = [
                    'success' => false,
                    'data' => 'Vui lòng lấy mã xác nhận trước khi thực hiện khôi phục!',
                ];
                return response()->json($res);
            }
            $code = $verify_code->code;
            if ($request->verify_code == $code) {
                $user = User::findOrFail($user_id);
                $newPassword = '';
                for ($i = 0; $i < 6; $i++) {
                    $newPassword .= mt_rand(0, 9);
                }
                $user->password_confirmation = Hash::make($newPassword);
                $user->save();
                $res = [
                    'success' => true,
                    'data' => 'Mật khẩu cấp 2 mới của bạn là: '.$newPassword,
                    'newPassword' => $newPassword,
                ];
                return response()->json($res, 200);
            }else {
                $res = [
                    'success' => false,
                    'data' => 'Mã xác nhận sai, vui lòng kiểm tra lại!',
                ];
                return response()->json($res);
            }
        }
    }

}
