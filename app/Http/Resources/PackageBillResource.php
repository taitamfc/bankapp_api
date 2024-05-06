<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use App\Models\UserBillPackage;


class PackageBillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['price_fm'] = number_format($data['price']);
        $user_id = Auth::guard('api')->id();
        $is_package_bill = UserBillPackage::where('user_id',$user_id)->first();
        if ($is_package_bill != null && $is_package_bill->type == $data['type']) {
            $data['is_package_bill'] = true;
        }else{
            $data['is_package_bill'] = false;
        }
        return $data;
    }
}
