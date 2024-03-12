<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        $data['create_at_fm'] = date('d/m/Y',strtotime($data['created_at']));
        $data['account_balance_fm'] = number_format($data['account_balance']);
        return $data;
    }
}
