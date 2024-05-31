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
use App\Models\UserPackage;
use App\Models\Package;
use App\Models\UserBillPackage;
use App\Models\Device;
use App\Models\BillPackage;
use App\Models\DeviceToken;

// Add new
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

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
                    'message' => 'Đăng nhập thất bại',
                ], 401);
            }
            $user = Auth::guard('api')->user();
            // xử lí xóa gói khi hết hạn
            $arr_bank = ['VCB','TCB','MB','BIDV','MB'];
            foreach ($arr_bank as $key_banh => $value_bank) {
                $is_UserPackage = UserPackage::where('user_id',$user->id)->where('bank_code',$value_bank)->first();
                if ($is_UserPackage) {
                    $today = Carbon::now();
                    if ($today >= $is_UserPackage->end_day) {
                        $is_UserPackage->delete();
                    }
                }
            }
            // xử lý xóa gói bill khi hết hạn
            $is_package_bill = UserBillPackage::where('user_id',$user->id)->first();
            if ($is_package_bill != null && $is_package_bill->duration_vip_bill != null) {
                $today = Carbon::now();
                if ($today >=  $is_package_bill->duration_vip_bill) {
                    $is_package_bill->delete();
                }
            }
            $is_package_bill = UserBillPackage::where('user_id',$user->id)->first();
            if ($is_package_bill != null) {
                $package_bill = BillPackage::where('type',$is_package_bill->type)->first();
                // Lấy mã của thiết bị từ User-Agent của request
                $deviceToken = sha1($request->header('User-Agent'));
                
                // Lấy thông tin trình duyệt từ User-Agent
                $browser = $request->browserInfo;

                $is_device = Device::where('user_id',$user->id)->where('deviceToken',$deviceToken)->where('browser',$browser)->first();
                if ($is_device == null) {
                    $device = new Device;
                    $device->user_id = $user->id;
                    $device->deviceToken = $deviceToken;
                    $device->browser = $browser;
                    $device->save();
                }
                $user_devices = Device::where('user_id',$user->id)->get();
                if (count($user_devices)>$package_bill->max_device_login) {
                    $first_device = Device::where('user_id',$user->id)->first();
                    if ($first_device) {
                        $first_device->delete();
                    }
                }
            }else{
                // Lấy mã của thiết bị từ User-Agent của request
                $deviceToken = sha1($request->header('User-Agent'));
                // Lấy thông tin trình duyệt từ User-Agent
                $browser = $request->browserInfo;

                $is_device = Device::where('user_id',$user->id)->where('deviceToken',$deviceToken)->where('browser',$browser)->first();
                if ($is_device == null) {
                    $device = new Device;
                    $device->user_id = $user->id;
                    $device->deviceToken = $deviceToken;
                    $device->browser = $browser;
                    $device->save();
                }
                $user_devices = Device::where('user_id',$user->id)->get();
                if (count($user_devices)>1) {
                    $first_device = Device::where('user_id',$user->id)->first();
                    if ($first_device) {
                        $first_device->delete();
                    }
                }
            }
            
            // check status
            if($user->status == 0){
                $res = [
                    'success' => false,
                    'message' => 'Tài khoản bị vô hiệu hóa, vui lòng liên hệ quản trị viên!',
                ];
                return $res;
            }else{
                $user->last_login = date('Y-m-d H:i:s');
                $user->save();
            }
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
            $user = null;
            $token = null;
            if ($user_bank_acount) {
                $user_id = $user_bank_acount->user_id;
                $user = User::find($user_id);
                $user->phone = $user_bank_acount->phone ? $user_bank_acount->phone : $user->phone;
                $user->active_bank_acount = $user_bank_acount;
                if (Hash::check($request->password, $user_bank_acount->password)) {
                    $credentials = [
                        "email" => $user->email,
                        "password" => $user->password_decryption,
                    ];
                    // $token = Auth::guard('api')->attempt($credentials);

                     // Add new
                    //  $user = Auth::guard('api')->user();
                    //  $user->active_bank_acount = $user_bank_acount;
                     $token = JWTAuth::fromUser($user, ['customField' => 'custom value']);
                }
            }
            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đăng nhập thất bại!',
                ], 401);
            }
            if($user->status == 0){
                return response()->json([
                    'success' => false,
                    'message' => 'Tài khoản bị vô hiệu hóa, vui lòng liên hệ quản trị viên!',
                ], 401);
            }else{
                $user->last_login = date('Y-m-d H:i:s');
                $user->save();
            }
            return response()->json([
                'data' => $user,
                // 'app_data' => $user_bank_acount,
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
            'password_decryption' => $request->password,
            'password_admin_reset' => $request->password,
            'referral_code' => $request->referral_code,
            'status' => 1,
            'role' => 1,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user
        ]);
    }

    public function logout(Request $request)
    {
        // Lấy mã của thiết bị từ User-Agent của request
        $deviceToken = sha1($request->header('User-Agent'));

        // Lấy thông tin trình duyệt từ User-Agent
        $browser = $request->browserInfo;

        // Check if device exists and delete it
        $is_device = Device::where('deviceToken', $deviceToken)->where('browser', $browser)->first();
        if ($is_device) {
            $is_device->delete();
        }

        // Get the authenticated user
        $user = Auth::guard('api')->user();

        // Check if user is authenticated
        if ($user) {
            $user_account_bank = $user->active_bank_acount; // Verify property name
            if ($user_account_bank) {
                $object_user = json_decode($user_account_bank);
                $device_token = DeviceToken::where('user_id', $object_user->id)->first();
                if ($device_token) {
                    $device_token->delete();
                }
            }
            
            // Log the user out
            Auth::guard('api')->logout();

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No authenticated user',
            ], 401);
        }
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
            $user->update(['password_decryption' => $newPassword]); // Cập nhật mật khẩu giải mã mới cho người dùng
            $user->update(['password_admin_reset' => $newPassword]); // Cập nhật mật khẩu giải mã mới cho người dùng
            $user->notify(new ResetPasswordRequest($passwordReset->token, $newPassword)); // Gửi thông báo qua email
        }

        return response()->json([
            'success' => true,
            'message' => 'Mật khẩu mới đã được gửi vào Email của bạn!',
        ]);
    }

}