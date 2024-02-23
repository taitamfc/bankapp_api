<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\UserBank;
use Illuminate\Support\Facades\Auth;

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
        $user_bank = UserBank::where('user_id',Auth::id())->where('bank_id', $bank['id'])->first();
        if ($user_bank != null) {
            $bank['user_status'] = $user_bank->user_status;
            return $bank;
        }else {
            $bank['user_status'] = 0;
            return $bank;
        }
    }
}
