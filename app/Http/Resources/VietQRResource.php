<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\VietqrBank;

class VietQRResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        dd($data['bin']);
        $vietQR_bank = VietqrBank::where('bin',$data['bin'])->first();
        $data['logo'] = $vietQR_bank->logo;
        return $data;

    }
}
