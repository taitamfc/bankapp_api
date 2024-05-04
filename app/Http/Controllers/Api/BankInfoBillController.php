<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\BankInfoBillResource;
use App\Models\BankInfoBill;
use App\Models\BillPackage;
use App\Models\UserBillPackage;
use App\Models\Device;
use Illuminate\Support\Facades\Auth;
use DateTime;



class BankInfoBillController extends Controller
{
    public function index()
    {
        $data = BankInfoBillResource::collection(BankInfoBill::all());
        $user = Auth::guard('api')->user();
        $is_package_bill = UserBillPackage::where('user_id',$user->id)->first();
        if ($is_package_bill != null) {
            $package_bill = BillPackage::where('type',$is_package_bill->type)->first();
            if ($is_package_bill->duration_vip_bill != null) {
                $currentDate = date('Y-m-d'); // Ngày hiện tại
                $endDate = new DateTime($is_package_bill->duration_vip_bill);
                $today = new DateTime($currentDate);
                $interval = $today->diff($endDate);
                $daysRemaining = $interval->format('%a');
                $is_package_bill->duration_vip_bill = $daysRemaining;
            }
            $user_devices = Device::where('user_id',$user->id)->get();
            $is_package_bill->max_login_device = count($user_devices);
            $res = [
                'success' => true,
                'data' => [
                    'data' => $data,
                    'is_package_bill' => $is_package_bill,
                    'package_bill' => $package_bill
                ],
            ];
        }else{
            $res = [
                'success' => true,
                'data' => [
                    'data' => $data,
                ],
            ];
        }
        return $res;
    }
}
