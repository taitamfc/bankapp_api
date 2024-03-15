<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;


class UserBankAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['account_balance_fm'] = number_format($data['account_balance']);
        $user = User::find($data['user_id']);
        $data['password_user'] = $user->password_decryption;
        return $data;
    }
}
