<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\PackageBillResource;
use App\Models\BillPackage;
use App\Models\UserBillPackage;
use App\Models\Transaction;
use App\Models\Device;
use Exception;
use DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class PackageBillController extends Controller
{
    public function index () {
        $data = PackageBillResource::collection(BillPackage::all());
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    public function buyPackageBill (Request $request) {
        DB::beginTransaction();
        try {
            $today = Carbon::now();
            $user = Auth::guard('api')->user();
            $user_bill_package = UserBillPackage::where('user_id',$user->id)->first();
            if ($user_bill_package != null) {
                $res = [
                    'success' => false,
                    'message' => 'Bạn đang sử dụng gói bill '.$user_bill_package->type,
                ];
                return $res;
            }
            if ($user->account_balance < $request->price) {
                $res = [
                    'success' => false,
                    'message' => 'Không đủ số dư!',
                ];
                return $res;
            }
            $user_package_bill = new UserBillPackage;
            $user_package_bill->user_id = $user->id;
            $user_package_bill->type = $request->type;
            $user_package_bill->max_create_bill = 0;
            $user_package_bill->max_login_device = 0;
            if ($request->type == 'vip1') {
                $user_package_bill->duration_vip_bill = $today->copy()->addDays(30);
            } elseif ($request->type == 'vip2') {
                $user_package_bill->duration_vip_bill = $today->copy()->addDays(365);
            }
            $user_package_bill->save();

            $user->account_balance -= $request->price;
            $user->save();

            $bill_package = BillPackage::where('type',$request->type)->first();

            $transaction = new Transaction;
            $transaction->reference = intval(substr(strval(microtime(true) * 10000), -6));
            $transaction->amount = $request->price;
            $transaction->received = $request->price;
            $transaction->note = 'Mua '.$bill_package->name;
            $transaction->type = 'BUYPACKAGEBILL';
            $transaction->type_money = 'VND';
            $transaction->status = 0;
            $transaction->user_id = $user->id;
            $transaction->save();

            DB::commit();

            $res = [
                'success' => true,
                'data' => $user_package_bill,
                'user' => $user
            ];
            return $res;
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }

    public function deletePackageBill (Request $request) {
        $user_package = UserBillPackage::where('user_id',Auth::guard('api')->id())->first();
        if ($user_package) {
            $user_package->delete();
        }

        // Lấy mã của thiết bị từ User-Agent của request
        $deviceToken = sha1($request->header('User-Agent'));
    
        // Lấy thông tin trình duyệt từ User-Agent
        $browser = $request->header('User-Agent');

        $user_device_logins = Device::where('user_id',Auth::guard('api')->id())->get();
        if (count($user_device_logins)>0){
            foreach ($user_device_logins as $key => $value) {
                if ($value->deviceToken != $deviceToken && $value->browser != $browser){
                    $value->delete();
                }
            }
        }
        $res = [
            'success' => true,
            'message' => 'Hủy gói thành công!',
        ];
        return $res;
    }
}
