<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginAdminRequest;
use App\Http\Requests\PasswordRequest;
use App\Http\Requests\SecondPasswordRequest;
use App\Http\Requests\RegisterAdminRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\OtpPasswordRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Models\PasswordReset;
use App\Notifications\ResetPasswordRequest;
use App\Notifications\PasswordNotification;
use App\Notifications\SecondPassNotification;
use App\Models\VerifyCode;


class AuthController extends Controller
{
    /**
     * Create token password reset.
     *
     * @param  ResetPasswordRequest $request
     * @return JsonResponse
     */
    public function __construct()
    {
        // $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    
    public function username()
    {
        return 'phone';
    }
    
    public function login(LoginAdminRequest $request)
    {
        $credentials = $request->only('phone', 'password');
        $token = Auth::attempt($credentials);
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        return response()->json([
            'data' => $user,
            'success' => true,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(RegisterAdminRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'password_confirmation' => Hash::make($request->password_confirmation),
            'referral_code' => $request->referral_code
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function changepassword(PasswordRequest $request)
    {
        $user = User::findOrFail(Auth::id());
        if (Hash::check($request->old_password, $user->password)) {
                $user->password = Hash::make($request->new_password);
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

    public function secondpassword(SecondPasswordRequest $request)
    {
        $user = User::findOrFail(Auth::id());
        if (Hash::check($request->old_password, $user->password_confirmation)) {
                $user->password_confirmation = Hash::make($request->new_password);
                return response()->json([
                    'success' => true,
                    'message' => 'Cập Nhật Mật Khẩu Cấp 2 Thành Công!',
                    'data' => $user
                ]);
        }else {
            return response()->json([
                'success' => false,
                'message' => 'Thất bại, mật khẩu cũ không đúng!',
            ]);
        }
    }
    public function sendMailResetPassword(ForgotPasswordRequest $request)
    {
        $user = User::where('email', $request->email)->firstOrFail();
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            ['token' => Str::random(60)]
        );

        if ($passwordReset) {
            $newPassword = Str::random(6); // Tạo mật khẩu mới
            $user->update(['password' => bcrypt($newPassword)]); // Cập nhật mật khẩu mới cho người dùng
            $user->notify(new ResetPasswordRequest($passwordReset->token, $newPassword)); // Gửi thông báo qua email
        }

        return response()->json([
            'success' => true,
            'message' => 'Mật khẩu mới đã được gửi vào Email của bạn!',
        ]);
    }

    public function sendMailPassOtp(Request $request)
    {
        $user = User::where('id', Auth::id())->firstOrFail();  
        $code = mt_rand(100000, 999999);       
        $verify_code = new VerifyCode;
        $verify_code->type = 'PASSWORD';
        $verify_code->code = $code;
        $verify_code->user_id = Auth::id();
        $verify_code->save();
        $user->notify(new PasswordNotification($code));
        return response()->json([
            'success' => true,
            'message' => 'Mã xác nhận khôi phục mật khẩu đã được gửi vào Email của bạn!',
        ]);
    }

    public function sendMailSecondPass(Request $request)
    {
        $user = User::where('id', Auth::id())->firstOrFail();  
        $code = mt_rand(100000, 999999);       
        $verify_code = new VerifyCode;
        $verify_code->type = 'SECONDPASS';
        $verify_code->code = $code;
        $verify_code->user_id = Auth::id();
        $verify_code->save();
        $user->notify(new SecondPassNotification($code));
        return response()->json([
            'success' => true,
            'message' => 'Mã xác nhận khôi phục mật khẩu cấp 2 đã được gửi vào Email của bạn!',
        ]);
    }

    public function resetPasswordOtp(OtpPasswordRequest $request)
    {
        $user_id = Auth::id();
        $verify_code = VerifyCode::where('user_id', $user_id)
                        ->where('type', 'PASSWORD')
                        ->orderBy('id', 'desc')
                        ->first();
        $code = $verify_code->code;
        if ($request->verify_code == $code) {
            $user = User::findOrFail($user_id);
            $newPassword = Str::random(6);
            $user->password = Hash::make($newPassword);
            $user->save();
            $res = [
                'success' => true,
                'message' => 'Mật khẩu mới của bạn là:'.$newPassword,
            ];
            return response()->json($res, 200);
        }else {
            $res = [
                'success' => false,
                'message' => 'Mã xác nhận sai, vui lòng kiểm tra lại!',
            ];
            return response()->json($res);
        }
    }

    public function resetSecondPasswordOtp(OtpPasswordRequest $request)
    {
        $user_id = Auth::id();
        $verify_code = VerifyCode::where('user_id', $user_id)
                        ->where('type', 'SECONDPASS')
                        ->orderBy('id', 'desc')
                        ->first();
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
                'message' => 'Mật khẩu cấp 2 mới của bạn là:'.$newPassword,
            ];
            return response()->json($res, 200);
        }else {
            $res = [
                'success' => false,
                'message' => 'Mã xác nhận sai, vui lòng kiểm tra lại!',
            ];
            return response()->json($res);
        }
    }
}
