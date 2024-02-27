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
            'user_name' => $request->user_name,
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
