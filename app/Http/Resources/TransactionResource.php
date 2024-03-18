<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $user = User::find($data['user_id']);
        $data['name_user'] = $user->name;
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        $data['create_at_fm'] = date('H:i:s d/m/Y', strtotime($data['created_at']));
        $data['received_fm'] = number_format($data['received']);
        $data['amount_fm'] = number_format($data['amount']);
        return $data;
    }
}
