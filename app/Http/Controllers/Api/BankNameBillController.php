<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankNameBill;
use App\Http\Resources\BankNameBillResource;

class BankNameBillController extends Controller
{
    public function index(Request $request)
    {
        $query = BankNameBill::where('type', $request->type);
        $item = $query->get();
        $data = BankNameBillResource::collection($item);
        $res = [
            'success' => true,
            'data' => $data,
        ];
        return $res;
    }
}
