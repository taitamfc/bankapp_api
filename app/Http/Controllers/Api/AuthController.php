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

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
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
}
