<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPackage;
use App\Models\UserBankAccount;
use App\Http\Resources\PackageResource;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Transaction;
use DB;

class PackageController extends Controller
{
    public function index(Request $request)
    {
        $query = Package::where('bank_code',$request->bank_code);
        $data = PackageResource::collection($query->get());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    public function buyPackage (Request $request) 
    {
        DB::beginTransaction();
        try {
            $is_user_packages = UserPackage::where('user_id',Auth::guard('api')->id())->where('bank_code',$request->bank_code)->first();
            
            if ($is_user_packages != null && $is_user_packages->package_all != "ALL") {
                $res = [
                    'success' => false,
                    'message' => 'Bạn đang sử dụng gói '.$is_user_packages->type_package.' của gói '.$request->bank_code.'!',
                ];
                return $res;
            }elseif ($is_user_packages != null && $is_user_packages->package_all == "ALL") {
                $res = [
                    'success' => false,
                    'message' => 'Bạn đang sử dụng gói '.$is_user_packages->type_package.' của gói Đặc Biệt!',
                ];
                return $res;
            }
            
            if ($request->bank_code == 'ALL') {
                $arr_user_packages = UserPackage::where('user_id',Auth::guard('api')->id())->get();
                if (count($arr_user_packages)>0) {
                    foreach ($arr_user_packages as $key_arr_user_package => $value_arr_user_package) {
                        if ($value_arr_user_package->package_all != 'ALL') {
                            $value_arr_user_package->delete();  
                        }else{
                            $res = [
                                'success' => false,
                                'message' => 'Bạn đang sử dụng gói '.$value_arr_user_package->type_package.' của gói Đặc Biệt!',
                            ];
                            return $res;
                        }
                    }
                }
            }
            $package = Package::where('type',$request->type_package)->where('bank_code',$request->bank_code)->first();
            $today = Carbon::now();
            $user = Auth::guard('api')->user();
            $user_package = UserPackage::where('user_id',$user->id)->where('bank_code',$request->bank_code)->first();
            if($user_package == null) {
                if ($user->account_balance < $package->price) {
                    $res = [
                        'success' => false,
                        'message' => 'Bạn Không đủ tiền mua gói!',
                    ];
                    return $res;
                }
                $count_user_bank_account = UserBankAccount::where('user_id', Auth::guard('api')->id())
                ->where('type', $request->bank_code)->count();
                //trừ tiền
                $user->account_balance -= $package->price;
                $user->save();
                // lưu vào bảng trung gian
                if ($request->bank_code == "ALL") {
                    $arr_all_banks = ['VCB','TCB','MB','ICB','BIDV'];
                    foreach ($arr_all_banks as $key_arr_all_bank => $value_arr_all_bank) {
                        $user_package = new UserPackage;
                        $user_package->type_package = $request->type_package;
                        $user_package->user_id =$user->id;
                        $user_package->bank_code = $value_arr_all_bank;
                        $user_package->package_all = 'ALL';
                        $user_package->start_day = $today;
                        $user_package->end_day = $today->copy()->addDays(30);
                        if ($count_user_bank_account > 0) {
                            $user_package->total_create_account = $count_user_bank_account;
                        }else {
                            $user_package->total_create_account = 0;
                        }
                        $user_package->total_edit_account = 0;
                        $user_package->total_transfer_app = 0;
                        $user_package->total_deposit_app = 0;
                        $user_package->save();
                    }
                }else{
                    $user_package = new UserPackage;
                    $user_package->type_package = $request->type_package;
                    $user_package->user_id =$user->id;
                    $user_package->bank_code = $request->bank_code;
                    $user_package->start_day = $today;
                    $user_package->end_day = $today->copy()->addDays(30);
                    if ($count_user_bank_account > 0) {
                        $user_package->total_create_account = $count_user_bank_account;
                    }else {
                        $user_package->total_create_account = 0;
                    }
                    $user_package->total_edit_account = 0;
                    $user_package->total_transfer_app = 0;
                    $user_package->total_deposit_app = 0;
                    $user_package->save();
                }
                // lưu vào lịch sử
                $transaction = new Transaction;
                $transaction->reference = intval(substr(strval(microtime(true) * 10000), -6));
                $transaction->amount = $package->price;
                $transaction->received = $package->price;
                $transaction->note = 'Mua gói '.$package->name.' ngân hàng '.$request->bank_code;
                $transaction->type = 'BUYPACKAGE';
                $transaction->type_money = 'VND';
                $transaction->status = 0;
                $transaction->user_id = $user->id;
                $transaction->save();

                DB::commit();
                $res = [
                    'success' => true,
                    'data' => $user,
                    'transaction' => $transaction,
                ];
                return $res;
            }else{
                $res = [
                    'success' => false,
                    'message' => 'Tài khoản đã mua gói '.$user_package->type_package.' với ngân hàng '.$request->bank_code,
                ];
                return $res;
            }
            
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function curentPackageApp (Request $request) {
        $is_user_packages = UserPackage::where('user_id',Auth::guard('api')->id())->get();
        $items = [];
        if (count($is_user_packages)>0 && $is_user_packages[0]->package_all != null) {
            $items =[
                [
                    'type_package' => $is_user_packages[0]->type_package,
                    'package_all' => $is_user_packages[0]->package_all,
                    'bank_code' => 'ALL',
                ],
            ];
                
            $res = [
                'success' => true,
                'is_all' => true,
                'data' => $items,
            ];
            return $res;
        }else{
            $res = [
                'success' => true,
                'is_all' => false,
                'data' => $is_user_packages,
            ];
            return $res;
        }
    }

    public function deletePackageApp (Request $request) {
        if ($request->bank_code != 'ALL') {
            $user_package = UserPackage::where('user_id',Auth::guard('api')->id())->where('bank_code',$request->bank_code)->first();
            if ($user_package != null) {
                $user_package->delete();
            }
            $res = [
                'success' => true,
                'message' => 'Hủy gói thành công!',
            ];
            return $res;
        }else{
            $user_packages = UserPackage::where('user_id',Auth::guard('api')->id())->get();
            foreach ($user_packages as $key => $value) {
                $value->delete();
            }
            $res = [
                'success' => true,
                'message' => 'Hủy gói thành công!',
            ];
            return $res;
        }
    }

}
