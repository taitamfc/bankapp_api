<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\OwnerBankResource;
use App\Models\OwnerBank;
use Illuminate\Support\Facades\Http;


class OwnerBankController extends Controller
{
    public function index()
    {
        $data = OwnerBankResource::collection(OwnerBank::paginate(5));
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }

    public function getQRdeposit(Request $request)
    {
        $param = [
            "accountNo" => $request->accountNo,
            "accountName"=> $request->accountName,
            "acqId"=> $request->acqId,
            "amount"=> $request->amount,
            "addInfo"=> $request->addInfo,
            "format"=> $request->format,
            "template"=> $request->template,
        ];
        $apiCheckUrl = "https://api.vietqr.io/v2/generate";
        $bankApiKey = env('BANK_API_KEY');
        $bankApiClientId = env('BANK_API_CLIENT_ID');
        $response = Http::withHeaders([
        'x-api-key' => $bankApiKey,
        'x-client-id' => $bankApiClientId,
        ])->withBody(json_encode($param), 'application/json')->post($apiCheckUrl);
        $result = $response->json();
        return $result;
    }
}
