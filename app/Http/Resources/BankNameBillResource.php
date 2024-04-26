<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BankNameBillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);
        if ($request->type == "VCB") {
            $data['name_fm'] = $data['name']." (".$data['shortName'].")";
        }
        if ($request->type == "TPB") {
            $data['name_fm'] = $data['shortName'];
        }
        if ($request->type == "TCB") {
            $data['name_fm'] = $data['name'];
        }
        if ($request->type == "MB") {
            $data['name_fm'] = $data['name']." (".$data['code'].")";
        }
        if ($request->type == "ACB") {
            $data['name_fm'] = $data['shortName'].' - '.$data['name'];
        }
        if ($request->type == "ICB") {
            $data['name_fm'] = $data['name'];
        }
        if ($request->type == "BIDV") {
            $data['name_fm'] = $data['name'];
        }
        if ($request->type == "VBA") {
            $data['name_fm'] = $data['shortName'];
        }
        if ($request->type == "VPB") {
            $data['name_fm'] = $data['shortName'];
        }
        if ($request->type == "STB") {
            $data['name_fm'] = $data['shortName'];
        }
        if ($request->type == "MSB") {
            $data['name_fm'] = $data['shortName'];
        }   
        return $data;
    }
}
