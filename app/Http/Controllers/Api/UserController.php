<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserBankAccount;
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
use App\Models\Transaction;
use App\Http\Requests\OtpPasswordRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\BillPackage;
use App\Models\UserBillPackage;
use App\Models\HistoryBillFree;
use Carbon\Carbon;
use DateTime;


// Add new
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    public function index(Request $request){
        $page = $request->input('page', 1); // Trang mặc định là 1 nếu không được truyền vào
        $perPage = $request->input('perPage', 5); // Số lượng mục dữ liệu mỗi trang mặc định là
        $query = User::query(true);
        if ($request->search) {
            $search_user = $request->search;
            $user_name = $search_user['user_name'];
            if( $user_name ){
                $query->where('name',  'LIKE',"%" .$user_name. "%");
                $query->orWhere('user_name',  'LIKE',"%" .$user_name. "%");
                $query->orWhere('email',  'LIKE',"%" .$user_name. "%");
            }

        }
        $items = $query->paginate($perPage, ['*'], 'page', $page);
        $users = UserResource::collection($items);
        $res = [
            'success' => true,
            'data' => $users,
            'meta' => [
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
            ],
        ];
        return $res;
    }

    public function updateRole(Request $request){
        $user = User::find($request->user_id);
        $user->role = $request->role;
        $user->save();
        $res = [
            'success' => true,
            'data' => $user,
        ];
        return $res;
    }

    public function delete($id){
        $user = User::find($id);

        if ($user) {
            $user->delete();
    
            return response()->json([
                'message' => 'Đã xóa thành công',
            ]);
        } else {
            return response()->json([
                'message' => 'Không tìm thấy người dùng',
            ], 404);
        }
    }

    public function showUserOfAdmin(Request $request)
    {
        $data = new UserResource(User::findOrFail($request->id));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

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
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật hồ sơ thành công!',
            'data' => $user
        ]);
    }

    public function adminUpdate(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $user->name = $request->name;
        $user->user_name = $request->user_name;
        $user->email = $request->email;
        $user->account_balance = $request->account_balance;
        if ($request->password != null) {
            $user->password = Hash::make($request->password);
            $user->password_admin_reset = $request->password;
            $user->password_decryption = $request->password;
        }
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
                    $user->password_decryption = $request->new_password;
                    $user->password_admin_reset = $request->new_password;
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
                $user->password_decryption = $newPassword;
                $user->password_admin_reset = $newPassword;
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

    public function updateBlanceUser (Request $request) {
        $user = Auth::guard('api')->user();
        $user->account_balance += $request->amount;
        $user->save();
    }

    public function depositAppHandmade (Request $request) {
        $user = User::find($request->user_id);
        $user->account_balance += $request->amount;
        $user->save();

        // Cộng 10% số tiền nạp tiền cho tài khoản giới thiệu
        if($user){
            $parent_user = User::where('user_name',$user->referral_code)->first();
            if( $parent_user ){
                $pr_referral_account_balance = $parent_user->referral_account_balance;
                $parent_user->referral_account_balance = (float)$pr_referral_account_balance + ($request->amount/100*10);
                $parent_user->save();
            }
        }

        // lưu vào lịch sử
        $user_id = $request->user_id;
        $transaction = new Transaction;
        $transaction->reference = 6;
        $transaction->amount = $request->amount;
        $transaction->received = $request->amount;
        $transaction->type = 'DEPOSITFROMADMIN';
        $transaction->type_money = 'VND';
        $transaction->status = 1;
        $transaction->user_id = $user_id;
        $transaction->note = "Nạp tiền từ quản trị viên";
        $transaction->save();
        $res = [
            'success' => true,
            'data' => $user,
            'amount' => number_format($request->amount),
        ];
        return response()->json($res);
    }

    public function updateStatus (Request $request) {
        $user = User::find($request->user_id);
        $user->status = $request->status;
        $user->save();
        $res = [
            'success' => true,
            'data' => $user,
        ];
        return response()->json($res);
    }

    public function updateEmail (Request $request) {
        $count_email = User::where('email',$request->email)->count();
        if ($count_email > 0) {
            $res = [
                'success' => false,
                'data' => 'Email đã được đăng ký trong hệ thống!',
            ];
            return response()->json($res);
        }
        $user = Auth::guard('api')->user();
        $verify_code = VerifyCode::where('user_id', $user->id)
                        ->where('type', 'CHANGEMAIL')
                        ->where('email', $request->email)
                        ->orderBy('id', 'desc')
                        ->first();
        if($verify_code == null){
            $res = [
                'success' => false,
                'data' => 'Vui lòng lấy mã xác nhận trước khi thực hiện cập nhật!',
            ];
            return response()->json($res);
        }
        $code = $verify_code->code;
        if ($verify_code->email != $request->email) {
            $res = [
                'success' => false,
                'data' => 'Email không khớp với mã OTP này!',
            ];
            return response()->json($res);
        }
        if ($request->verify_code == $code) {
            $user = User::findOrFail($user->id);
            $user->email = $request->email;
            $user->save();
            $res = [
                'success' => true,
                'data' => $user,
                'message' => 'Cập nhật email thành công!',
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

    public function handleFeeDowloadBill (Request $request) {
        DB::beginTransaction();
        try {
            $currentDate = date('Y-m-d');
            $user = Auth::guard('api')->user();
            $is_package_bill = UserBillPackage::where('user_id',$user->id)->first();
            if ($is_package_bill != null) {
                $count_dow_free_before = HistoryBillFree::where('user_id', $user->id)
                    ->whereDate('created_at', $currentDate)
                    ->where('created_at', '>=' , $is_package_bill->created_at)
                    ->count();
                $package_bill = BillPackage::where('type',$is_package_bill->type)->first();
                if ($count_dow_free_before <= $package_bill->max_download_bill) {
                    // xử lý khi có vip
                    $history_dowload_free = new HistoryBillFree;
                    $history_dowload_free->user_id = $user->id;
                    $history_dowload_free->save();

                    
                    // $count_dow_free_after = HistoryBillFree::where('user_id',$user->id)->where('created_at',$today)->count();

                    // $is_package_bill->max_create_bill = $count_dow_free_after;
                    // $is_package_bill->save();

                    $base64Image = $request->imagePreview;
                    $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                    $filename = uniqid() . '.png';
                    $storagePath = 'billdownloaded/' . $filename;
                    Storage::disk('public')->put($storagePath, base64_decode($imageData));
        
                    DB::commit();
        
                    $res = [
                        'success' => true,
                        'data' => $user,
                        'imageUrl' => asset( 'storage/'.$storagePath ),
                    ];
                    return $res;
                }else{
                    // xử lý hết hạn vip
                    if ($user->account_balance < 19000) {
                        $res = [
                            'success' => false,
                            'messange' => 'Không đủ số dư để tải bill!',
                        ];
                        return $res;
                    }
                    $user->account_balance -= 19000;
                    $user->save();
        
                    $transaction = new Transaction;
                    $transaction->reference = intval(substr(strval(microtime(true) * 10000), -6));
                    $transaction->amount = 19000;
                    $transaction->received = 19000;
                    $transaction->type = 'DOWLOADBILL';
                    $transaction->type_money = 'VND';
                    $transaction->status = 1;
                    $transaction->note = "Phí tạo bill";
                    $transaction->user_id = $user->id;
                    $transaction->save();
        
        
                    $base64Image = $request->imagePreview;
                    $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                    $filename = uniqid() . '.png';
                    $storagePath = 'billdownloaded/' . $filename;
                    Storage::disk('public')->put($storagePath, base64_decode($imageData));
        
                    DB::commit();
        
                    $res = [
                        'success' => true,
                        'data' => $user,
                        'imageUrl' => asset( 'storage/'.$storagePath ),
                        'transaction' => $transaction,
                    ];
                    return $res;
                }
            }else {
                // xử lý chưa có vip
                if ($user->account_balance < 19000) {
                    $res = [
                        'success' => false,
                        'messange' => 'Không đủ số dư để tải bill!',
                    ];
                    return $res;
                }
                $user->account_balance -= 19000;
                $user->save();
    
                $transaction = new Transaction;
                $transaction->reference = intval(substr(strval(microtime(true) * 10000), -6));
                $transaction->amount = 19000;
                $transaction->received = 19000;
                $transaction->type = 'DOWLOADBILL';
                $transaction->type_money = 'VND';
                $transaction->status = 1;
                $transaction->note = "Phí tạo bill";
                $transaction->user_id = $user->id;
                $transaction->save();
    
    
                $base64Image = $request->imagePreview;
                $imageData = substr($base64Image, strpos($base64Image, ',') + 1);
                $filename = uniqid() . '.png';
                $storagePath = 'billdownloaded/' . $filename;
                Storage::disk('public')->put($storagePath, base64_decode($imageData));
    
                DB::commit();
    
                $res = [
                    'success' => true,
                    'data' => $user,
                    'imageUrl' => asset( 'storage/'.$storagePath ),
                    'transaction' => $transaction,
                ];
                return $res;
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function handleFeeDowloadNotification (Request $request) {
        $user = Auth::guard('api')->user();
        if ($user) {
            if ($user->account_balance >= 19000) {
                $user->account_balance -= 19000;
                $user->save();
                $res = [
                    'success' => true,
                    'message' => 'Tải bill thành công!',
                    'data' => $user,
                ];
                return $res;
            }else {
                $res = [
                    'success' => false,
                    'message' => 'Bạn không đủ số dư để tải tải ảnh!',
                ];
                return $res;
            }
        }else {
            $res = [
                'success' => false,
                'message' => 'Bạn chưa đăng nhập!',
            ];
            return $res;
        }
    }

    public function changeAvatar(Request $request)
    {
        $image = $request->file('file');

        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:jpg,jpeg,png|max:10240'
        ], [
            'file.required' => 'Avatar không được để trống.',
            'file.mimes' => 'Avatar không đúng định dạng.',
            'file.max' => 'Avatar tối đa 10mb.',
        ]);

        if ($validator->fails()) return response()->json([
            'success' => true,
            'message' => $validator->getMessageBag()->first(),
        ], 422);

        $token = JWTAuth::getToken();
        $user = JWTAuth::toUser($token);
        $userBankAccountJson = !empty($user->active_bank_acount) ? json_decode($user->active_bank_acount) : null ;

        try {
            return DB::transaction(function () use ($userBankAccountJson, $image, $token) {
                $userBankAccount = UserBankAccount::find(data_get($userBankAccountJson, 'id'));

                if(!empty($userBankAccount?->image) && Storage::disk('public')->exists($userBankAccount?->image)) Storage::disk('public')->delete($userBankAccount?->image);

                $filename = date('Ymdhis') . '_avatar.' . $image->getClientOriginalExtension();
                $path =  Storage::disk('public')->putFileAs('avatars',$image, $filename);

                $userBankAccount->image = $path;
                $userBankAccount->save();

                $userBankAccount->image = Storage::disk('public')->url($path);

                return response()->json(['status' => true, 'message' => 'Đổi avatar thành công.', 'data' => $userBankAccount]);
            });

        }catch (\Exception $exception){
            Log::error($exception->getMessage());
            return response()->json(['status' => false, 'message' => $exception->getMessage()]);
        }
    }

}
