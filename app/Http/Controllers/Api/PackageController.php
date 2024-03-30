<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\User;
use App\Models\UserPackage;
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
                //trừ tiền
                $user->account_balance -= $package->price;
                $user->save();
                // lưu vào bảng trung gian
                $user_package = new UserPackage;
                $user_package->type_package = $request->type_package;
                $user_package->user_id =$user->id;
                $user_package->bank_code = $request->bank_code;
                $user_package->start_day = $today;
                $user_package->end_day = $today->copy()->addDays(30);
                $user_package->total_create_account = 0;
                $user_package->total_edit_account = 0;
                $user_package->total_transfer_app = 0;
                $user_package->total_deposit_app = 0;
                $user_package->save();
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

}
