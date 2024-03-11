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
use App\Models\UserBankAccount;



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
        $type_web = $request->type_web ?? 'app';
        if ($type_web == 'web') {
            if (filter_var($request->name_login, FILTER_VALIDATE_EMAIL)) {
                $credentials = [
                    "email" => $request->name_login,
                    "password" => $request->password,
                ];
              } else {
                $credentials = [
                    "user_name" => $request->name_login,
                    "password" => $request->password,
                ];
            }
            $token = Auth::guard('api')->attempt($credentials);
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            $user = Auth::guard('api')->user();
            return response()->json([
                'data' => $user,
                'success' => true,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        }
        if ($type_web == 'app'){
            $phone = $request->phone;
            $type = $request->type;
            $user_bank_acount = UserBankAccount::where('phone',$phone)->where('type',$type)->first();
            if ($user_bank_acount) {
                $user_id = $user_bank_acount->user_id;
                $user = User::find($user_id);
                if (Hash::check($request->password, $user->password)) {
                    $credentials = [
                        "email" => $user->email,
                        "password" => $request->password,
                    ];
                    $token = Auth::guard('api')->attempt($credentials);
                }
            }
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 401);
            }
            return response()->json([
                'data' => $user,
                'app_data' => $user_bank_acount,
                'success' => true,
                'authorization' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } 
    }

    public function register(RegisterAdminRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'user_name' => $request->user_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
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
        Auth::guard('api')->logout();
        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    public function refresh()
    {
        return response()->json([
            'user' => Auth::guard('api')->user(),
            'authorisation' => [
                'token' => Auth::guard('api')->id()->refresh(),
                'type' => 'bearer',
            ]
        ]);
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

}