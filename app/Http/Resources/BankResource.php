<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserBank;
use Illuminate\Support\Facades\Auth;
use App\Models\UserPackage;
use App\Models\Package;
use DateTime;
class BankResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $bank = parent::toArray($request);
        $is_UserPackage = UserPackage::where('user_id',Auth::guard('api')->id())->where('bank_code',$bank['type'])->first();
        if ($is_UserPackage) {
            $package = Package::where('type',$is_UserPackage->type_package)->where('bank_code',$bank['type'])->first();
            $bank['package'] = $package;
            $bank['is_UserPackage'] = $is_UserPackage;
            $bank['package']['max_deposit_app_fm'] = number_format($bank['package']['max_deposit_app']);
            $bank['is_UserPackage']['total_deposit_app_fm'] = number_format($bank['is_UserPackage']['total_deposit_app']);
            // tính thời hạn gói
            $currentDate = date('Y-m-d'); // Ngày hiện tại
            $endDate = new DateTime($bank['is_UserPackage']['end_day']);
            $today = new DateTime($currentDate);
            $interval = $today->diff($endDate);
            $daysRemaining = $interval->format('%a');
            $bank['is_UserPackage']['duration_vip'] = $daysRemaining;
            //
            $user_bank = UserBank::where('user_id',Auth::guard('api')->id())->where('bank_id', $bank['id'])->first();
            if ($user_bank != null) {
                $bank['user_status'] = $user_bank->user_status;
                return $bank;
            }else {
                $bank['user_status'] = 0;
                return $bank;
            }
        }else{
            $bank['package'] = null;
            $bank['is_UserPackage'] = null;
            $user_bank = UserBank::where('user_id',Auth::guard('api')->id())->where('bank_id', $bank['id'])->first();
            if ($user_bank != null) {
                $bank['user_status'] = $user_bank->user_status;
                return $bank;
            }else {
                $bank['user_status'] = 0;
                return $bank;
            }
        }
    }
}
