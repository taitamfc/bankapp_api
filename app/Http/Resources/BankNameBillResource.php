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
        switch ($request->type) {
            case 'VCB':
                $data['name_fm'] = $data['name']." (".$data['shortName'].")";
                break;
            case 'TPB':
            case 'VBA':
            case 'VPB':
            case 'STB':
            case 'MSB':
                $data['name_fm'] = $data['shortName'];
                break;
            case 'TCB':
            case 'ICB':
            case 'BIDV':
                $data['name_fm'] = $data['name'];
                break;
            case 'MB':
                $data['name_fm'] = $data['name']." (".$data['code'].")";
                break;
            case 'ACB':
                $data['name_fm'] = $data['name_no_sign'];
                break;
            default:
                $data['name_fm'] = $data['shortName'];
                break;
        }
        return $data;
    }
}
